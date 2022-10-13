package middlewares

import (
	"errors"
	"gohub/pkg/response"

	"github.com/gin-gonic/gin"
)

//强制请求必须附带user-agent标头
func ForceUA() gin.HandlerFunc {
	return func(c *gin.Context) {
		//获取user-agent表头信息
		if len(c.Request.Header["User-Agent"]) == 0 {
			response.BadRequest(c, errors.New("User-Agent 标头找不到"), "请求必须附带user-agent标头")
			return
		}
		c.Next()
	}
}
