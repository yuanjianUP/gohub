package routes

import (
	"github.com/gin-gonic/gin"
	"gohub/app/http/controllers/api/v1/auth"
)

func RegisterAPIRoutes(router *gin.Engine) {
	v1 := router.Group("v1")
	{
		authGroup := v1.Group("auth")
		{
			SupCt := new(auth.SignupController)
			authGroup.POST("/signup/phone/exist", SupCt.IsPhoneExist)
			authGroup.POST("/signup/Email/exist", SupCt.IsEmailExist)
			vcc := new(auth.VerifyCodeController)
			authGroup.POST("/verify-codes/captcha", vcc.ShowCaptcha)
			authGroup.POST("/verify-codes/email", vcc.SendUsingEmail)
			authGroup.POST("/signup/using-email")
		}

	}
}
