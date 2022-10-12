package routes

import (
	controllers "gohub/app/http/controllers/api/v1"
	"gohub/app/http/controllers/api/v1/auth"
	"gohub/app/http/middlewares"

	"github.com/gin-gonic/gin"
)

func RegisterAPIRoutes(router *gin.Engine) {
	v1 := router.Group("/v1")
	v1.Use(middlewares.LimitIP("200-H"))
	{
		uc := new(controllers.UsersController)
		//获取当前用户
		v1.GET("/user", middlewares.AuthJWT(), uc.CurrentUser)
		usersGroup := v1.Group("/users")
		{
			usersGroup.GET("", uc.Index)
			usersGroup.PUT("", middlewares.AuthJWT(), uc.Update)
			usersGroup.PUT("/email", middlewares.AuthJWT(), uc.UpdateEmail)
			usersGroup.PUT("/phone", middlewares.AuthJWT(), uc.UpdatePhone)
			usersGroup.PUT("/password", middlewares.AuthJWT(), uc.UpdatePassword)
		}

		//分类
		cg := new(controllers.CategoriesController)
		cgcGroup := v1.Group("/categories")
		{
			cgcGroup.GET("", middlewares.AuthJWT(), cg.Index)
			cgcGroup.POST("", middlewares.AuthJWT(), cg.Store)
			cgcGroup.PUT("/:id", middlewares.AuthJWT(), cg.Update)
			cgcGroup.DELETE("/:id", middlewares.AuthJWT(), cg.Delete)
		}

		authGroup := v1.Group("/auth")
		authGroup.Use(middlewares.LimitIP("1000-H"))
		{
			SupCt := new(auth.SignupController)
			authGroup.POST("/signup/phone/exist", SupCt.IsPhoneExist)
			authGroup.POST("/signup/Email/exist", SupCt.IsEmailExist)
			authGroup.POST("/signup/using-email", SupCt.SignupUsingEmail) //注册用户

			vcc := new(auth.VerifyCodeController)
			authGroup.POST("/verify-codes/captcha", vcc.ShowCaptcha)
			authGroup.POST("/verify-codes/email", vcc.SendUsingEmail)

			loginCtr := new(auth.LoginController)

			authGroup.POST("/login/using-password", loginCtr.LoginByPassword)
			authGroup.POST("/login/refresh-token", loginCtr.RefreshToken)
			pac := new(auth.PasswordController)
			authGroup.POST("/password-reset/using-email", pac.ResetByEmail) //邮箱找回密码

		}

		lsc := new(controllers.LinksController)
		linksGroup := v1.Group("/links")
		{
			linksGroup.GET("", lsc.Index)
		}
	}
}
