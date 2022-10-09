package main

import (
	"fmt"
	"gohub/app/cmd"
	"gohub/app/cmd/make"
	"gohub/bootstrap"
	btsConfig "gohub/config"
	cmd2 "gohub/pkg/cmd"
	"gohub/pkg/config"
	"gohub/pkg/console"
	"os"

	"github.com/spf13/cobra"
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
			//初始化缓存
			bootstrap.SetupCache()
		},
	}
	//注册字命令
	rootCmd.AddCommand(
		cmd2.CmdServe,
		cmd.Cmdkey,
		cmd.CmdPlay,
		make.CmdMake,
		cmd.CmdMigrate,
		cmd.CmdDBSeed,
	)
	//配置默认运行web服务
	cmd.RegisterDefaultCmd(rootCmd, cmd2.CmdServe)
	//注册全局参数 --env
	cmd.RegisterGlobalFlags(rootCmd)

	if err := rootCmd.Execute(); err != nil {
		console.Exit(fmt.Sprintf("Failed to run app with %v: %s", os.Args, err.Error()))
	}
}
