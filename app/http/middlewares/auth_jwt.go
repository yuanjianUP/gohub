package middlewares

import (
	"fmt"
	"github.com/gin-gonic/gin"
	"gohub/app/models/user"
	"gohub/pkg/config"
	"gohub/pkg/jwt"
	"gohub/pkg/response"
)

func AuthJWT() gin.HandlerFunc {
	return func(c *gin.Context) {
		claims, err := jwt.NewJWT().ParserToken(c)
		if err != nil {
			response.Unauthorized(c, fmt.Sprintf("请查看%v相关的接口认证文档", config.GetString("app.name")))
			return
		}
		//解析成功设置用户信息
		userModel := user.Get(claims.UserID)
		if userModel.ID == 0 {
			response.Unauthorized(c, "找不到对应的用可能已经删除")
			return
		}
		//将用户信息存入gin.context里
		c.Set("current_user_id", userModel.ID)
		c.Set("current_user_name", userModel.Name)
		c.Set("current_user", userModel)
		c.Next()
	}
}
