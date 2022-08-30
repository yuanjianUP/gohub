package main

import (
	"flag"
	"fmt"
	"github.com/gin-gonic/gin"
	"github.com/spf13/cobra"
	"gohub/app/cmd"
	"gohub/bootstrap"
	btsConfig "gohub/config"
	cmd2 "gohub/pkg/cmd"
	"gohub/pkg/config"
	"gohub/pkg/console"
	"os"
)

func init() {
	//加载config下所有配置
	btsConfig.Initialize()
}
func main() {
	var rootCmd = &cobra.Command{
		Use:   "gohub",
		Short: "A simple forum project",
		Long:  `Default will run "serve" command, you can use "-h" flag to see all subcommands`,
		//rootCmd的所有字命令都会执行一下代码
		PersistentPreRun: func(command *cobra.Command, args []string) {
			//配置初始化，依赖命令行--env参数
			config.InitConfig(cmd.Env)
			//初始化logger
			bootstrap.SetupLogger()
			//初始化数据库
			bootstrap.SetupDB()
			//初始化redis
			bootstrap.SetupRedis()
		},
	}
	//注册字命令
	rootCmd.AddCommand(
		cmd2.CmdServe,
		cmd.Cmdkey,
		cmd.CmdPlay,
	)
	//配置默认运行web服务
	cmd.RegisterDefaultCmd(rootCmd, cmd2.CmdServe)
	//注册全局参数 --env
	cmd.RegisterGlobalFlags(rootCmd)
	if err := rootCmd.Execute(); err != nil {
		console.Exit(fmt.Sprintf("Failed to run app with %v: %s", os.Args, err.Error()))
	}
	var env string
	flag.StringVar(&env, "env", "", "加载 .env 文件，如 --env=testing 加载的是 .env.testing 文件")
	flag.Parse()
	config.InitConfig(env)
	//初始化logger
	bootstrap.SetupLogger()
	// 设置 gin 的运行模式，支持 debug, release, test
	// release 会屏蔽调试信息，官方建议生产环境中使用
	// 非 release 模式 gin 终端打印太多信息，干扰到我们程序中的 Log
	// 故此设置为 release，有特殊情况手动改为 debug 即可
	gin.SetMode(gin.ReleaseMode)
	bootstrap.SetupDB() //数据库
	route := gin.New()
	bootstrap.SetupRote(route)
	bootstrap.SetupRedis()
	err := route.Run(":" + config.Get("app.port"))
	if err != nil {
		fmt.Println(err.Error())
	}
}
