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
func (ctrl *UsersController) Index(c *gin.Context) {
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
