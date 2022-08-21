package auth

import (
	"github.com/gin-gonic/gin"
	v1 "gohub/app/http/controllers/api/v1"
	"gohub/app/models/user"
	"gohub/app/requests"
	"gohub/pkg/jwt"
	"gohub/pkg/response"
)

type SignupController struct {
	v1.BaseAPIController
}

func (sc *SignupController) IsPhoneExist(c *gin.Context) {
	request := requests.SignupPhoneExistRequest{}
	ok := requests.Validate(&request, c, requests.ValidateSignupPhoneExist)
	if !ok {
		return
	}
	response.JSON(c, gin.H{
		"exist": user.IsPhoneExist(request.Phone),
	})
}
func (sc *SignupController) IsEmailExist(c *gin.Context) {
	request := requests.SignupEmailExistRequest{}
	if ok := requests.Validate(&request, c, requests.ValidateSignupEmailExist); !ok {
		return
	}
	response.JSON(c, gin.H{
		"exist": user.IsEmailExist(request.Email),
	})
}
func (sc *SignupController) SignupUsingEmail(c *gin.Context) {
	//验证表单
	request := requests.SignupUsingEmailRequest{}
	ok := requests.Validate(&request, c, requests.SignupUsingEmail)
	response.JSON(c, gin.H{
		"data": ok,
	})
	if !ok {
		return
	}
	userModel := user.User{
		Name:     request.Name,
		Email:    request.Email,
		Password: request.Password,
	}
	userModel.Create()
	if userModel.ID > 0 {
		token := jwt.NewJWT().IssueToken(userModel.GetStringID(), userModel.Name)
		response.CreatedJSON(c, gin.H{
			"data":  userModel,
			"token": token,
		})
	} else {
		response.Abort500(c, "创建用户失败，请稍后尝试")
	}
}
