package v1

import (
	"gohub/app/models/user"
	"gohub/app/requests"
	"gohub/pkg/auth"
	"gohub/pkg/config"
	"gohub/pkg/file"
	"gohub/pkg/response"

	"github.com/gin-gonic/gin"
)

type UsersController struct {
	BaseAPIController
}

//当前登陆用户信息
func (ctr *UsersController) CurrentUser(c *gin.Context) {
	userModel := auth.CurrentUser(c)
	response.Data(c, userModel)
}
func (ctr *UsersController) Index(c *gin.Context) {
	request := requests.PaginationRequest{}

	err := requests.Validate(&request, c, requests.Pagination)

	if !err {
		return
	}

	data, paging := user.Paginate(c, 10)
	response.JSON(c, gin.H{
		"data":   data,
		"paging": paging,
	})
}

func (ctr *UsersController) Update(c *gin.Context) {
	request := requests.UserUpdateProfileRequest{}
	if ok := requests.Validate(&request, c, requests.UserUpdateProfile); !ok {
		return
	}
	currentModel := auth.CurrentUser(c)
	currentModel.Name = request.Name
	currentModel.City = request.City
	currentModel.Introduction = request.Introduction
	rowsAffected := currentModel.Save()
	if rowsAffected > 0 {
		response.Data(c, currentModel)
		return
	}
	response.Abort500(c, "跟新失败，请稍后再试")
}

func (ctr *UsersController) UpdateEmail(c *gin.Context) {
	request := requests.UserUpdateEmailRequest{}
	if ok := requests.Validate(&request, c, requests.UserUpdateEmail); !ok {
		return
	}
	currentModel := auth.CurrentUser(c)
	currentModel.Email = request.Email
	rowsAffected := currentModel.Save()
	if rowsAffected > 0 {
		response.Data(c, currentModel)
	} else {
		response.Abort500(c, "邮箱更新失败")
	}
}

func (ctr *UsersController) UpdatePhone(c *gin.Context) {
	request := requests.UserUpdatePhoneRequest{}
	if ok := requests.Validate(&request, c, requests.UserUpdatePhone); !ok {
		return
	}
	currentUser := auth.CurrentUser(c)
	currentUser.Phone = request.Phone
	rowsAffected := currentUser.Save()
	if rowsAffected > 0 {
		response.Success(c)
	} else {
		response.Abort500(c, "更新失败")
	}

}

func (ctr *UsersController) UpdatePassword(c *gin.Context) {
	request := requests.UserUpdatePassWordRequest{}
	if ok := requests.Validate(&request, c, requests.UserUpdatePassword); !ok {
		return
	}
	currentUser := auth.CurrentUser(c)
	//验证原始密码是否正确
	_, err := auth.Attempt(currentUser.Name, currentUser.Password)
	if err != nil {
		response.Unauthorized(c, "原密码不正确")
	} else {
		currentUser.Password = request.Password
		rowsAffceted := currentUser.Save()
		if rowsAffceted > 0 {
			response.Success(c)
		} else {
			response.Abort500(c, "更新失败")
		}
	}

}

func (ctrl *UsersController) UserUpdateAvatar(c *gin.Context) {
	request := requests.UserUpdateAvatarRequest{}
	if ok := requests.Validate(&request, c, requests.UserUpdateAvatar); !ok {
		return
	}
	avatar, err := file.SaveUploadAvator(c, request.Avatar)
	if err != nil {
		response.Abort500(c, "上传头像失败，请稍后尝试")
		return
	}
	currentUser := auth.CurrentUser(c)
	currentUser.Avatar = config.GetString("app.url") + avatar
	currentUser.Save()
	response.Data(c, currentUser)
}
