package v1

import (
	"gohub/app/models/category"
	"gohub/app/requests"
	"gohub/pkg/response"

	"github.com/gin-gonic/gin"
)

type CategoriesController struct {
	BaseAPIController
}

func (ctrl *CategoriesController) Store(c *gin.Context) {
	request := requests.CategoryRequest{}
	ok := requests.Validate(&request, c, requests.CategorySave)

	if !ok {
		return
	}
	categoryModel := category.Category{
		Name:        request.Name,
		Description: request.Description,
	}
	categoryModel.Create()
	if categoryModel.ID > 0 {
		response.Created(c, categoryModel)
	}
	response.Abort500(c, "创建失败,请稍后再试")
}
