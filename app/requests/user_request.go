package requests

import (
	"gohub/pkg/auth"

	"github.com/gin-gonic/gin"
	"github.com/thedevsaddam/govalidator"
)

type UserUpdateProfileRequest struct {
	Name         string `valid:"name" json:"name"`
	City         string `valid:"city" json:"city"`
	Introduction string `valid:"introduction" json:"introduction"`
}

func UserUpdateProfile(data interface{}, c *gin.Context) map[string][]string {
	uid := auth.CurrentUID(c)
	rules := govalidator.MapData{
		"name":         []string{"required", "alpha_num", "between:3,20", "not_exists:users,name," + uid},
		"city":         []string{"min_cn:2", "max_cn:20"},
		"introduction": []string{"min_cn:4", "max_cn:240"},
	}
	messages := govalidator.MapData{
		"name": []string{
			"required:名称为必填项",
			"alpha_num:用户格式错误，只允许数字和英文",
			"between:用户名长度需要3-20之间",
			"not_exists:用户名已被占用",
		},
		"city": []string{
			"min_cn:描述长度需至少 2 个字",
			"max_cn:描述长度不能超过 20 个字",
		},
		"introduction": []string{
			"min_cn:描述长度需至少 4 个字",
			"max_cn:描述长度不能超过 240 个字",
		},
	}
	return validate(data, rules, messages)
}
