package cmd

import (
	"github.com/gin-gonic/gin"
	"github.com/spf13/cobra"
	"gohub/bootstrap"
	"gohub/pkg/config"
	"gohub/pkg/console"
	"gohub/pkg/logger"
)

var CmdServe = &cobra.Command{
	Use:   "serve",
	Short: "Start web server",
	Run:   runWeb,
	Args:  cobra.NoArgs,
}

func runWeb(cmd *cobra.Command, args []string) {
	//设置gin的运行模式，支持debug,release,test
	//release会屏蔽调试信息，官方建议生产环境中使用
	//非release模式gin终端打印太多信息，干扰到我们程序中log
	//故此设置为release,有特殊情况手动改为debug	即可
	gin.SetMode(gin.ReleaseMode)
	router := gin.New()
	bootstrap.SetupRote(router)
	err := router.Run(":" + config.Get("app.port"))
	if err != nil {
		logger.ErrorString("CMD", "serve", err.Error())
		console.Exit("unable to start server,error:" + err.Error())
	}
}
