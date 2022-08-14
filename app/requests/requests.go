package requests

import (
	"fmt"
	"github.com/gin-gonic/gin"
	"github.com/thedevsaddam/govalidator"
	"gohub/pkg/response"
	"net/http"
)

type ValidateFun func(data interface{}, c *gin.Context) map[string][]string

func Validate(obj interface{}, c *gin.Context, handler ValidateFun) bool {
	if err := c.ShouldBindJSON(obj); err != nil { //接收参数
		c.AbortWithStatusJSON(http.StatusUnprocessableEntity, gin.H{
			"error": err.Error(),
		})
		fmt.Println(err.Error())
		return false
	}
	errs := handler(obj, c)

	if len(errs) > 0 {
		response.ValidationError(c, errs)
		return false
	}
	return true
}

func validate(data interface{}, rules govalidator.MapData, messages govalidator.MapData) map[string][]string {
	// 配置初始化
	opts := govalidator.Options{
		Data:          data,
		Rules:         rules,
		TagIdentifier: "valid", // 模型中的 Struct 标签标识符
		Messages:      messages,
	}

	// 开始验证
	return govalidator.New(opts).ValidateStruct()
}
