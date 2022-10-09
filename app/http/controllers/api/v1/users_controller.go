package v1

import (
	"gohub/app/models/user"
	"gohub/app/requests"
	"gohub/pkg/auth"
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
	}
	response.Abort500(c, "跟新失败，请稍后再试")
}
