package auth

import (
	"github.com/gin-gonic/gin"
	v1 "gohub/app/http/controllers/api/v1"
	"gohub/app/models/user"
	"gohub/app/requests"
	"gohub/pkg/response"
)

type PasswordController struct {
	v1.BaseAPIController
}

func (pc *PasswordController) ResetByEmail(c *gin.Context) {
	//验证表单
	request := requests.ResetByEmailRequest{}
	if ok := requests.Validate(&request, c, requests.ResetByEmail); !ok {
		return
	}
	//更新密码
	userModel := user.GetByMulti(request.Email)
	if userModel.ID == 0 {
		response.Abort404(c)
	} else {
		userModel.Password = request.Password
		userModel.Save()
		response.Success(c)
	}
}
