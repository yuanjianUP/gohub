package v1

import (
	"gohub/app/requests"

	"github.com/gin-gonic/gin"
)

type CategoriesController struct {
	BaseAPIController
}

func (ctrl *CategoriesController) Show(c *gin.Context) {
	request := requests.CategoryRequest{}
	err := requests.Validate(&request, c, requests.CategorySave)
	if !err {
		return
	}

}
