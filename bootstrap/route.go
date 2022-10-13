package bootstrap

import (
	"gohub/app/http/middlewares"
	"gohub/routes"
	"net/http"
	"strings"

	"github.com/gin-gonic/gin"
)

func SetupRote(router *gin.Engine) {
	//注册全局中间件
	registerGlobalMiddle(router)
	routes.RegisterAPIRoutes(router)
	seup404Handler(router)
}
func registerGlobalMiddle(route *gin.Engine) {
	route.Use(
		middlewares.Logger(),
		middlewares.Recovery(),
		middlewares.ForceUA(),
	)
}
func seup404Handler(router *gin.Engine) {
	router.NoRoute(func(c *gin.Context) {
		acceptString := c.Request.Header.Get("Accept")
		if strings.Contains(acceptString, "text/html") {
			c.String(http.StatusNotFound, "用户返回404")
		} else {
			c.JSON(http.StatusNotFound, gin.H{
				"error_code":  404,
				"err_message": "路由为定义",
			})
		}
	})
}
