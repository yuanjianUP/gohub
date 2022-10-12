package requests

import (
	"gohub/app/requests/validators"
	"gohub/pkg/auth"

	"github.com/gin-gonic/gin"
	"github.com/thedevsaddam/govalidator"
)

type UserUpdateProfileRequest struct {
	Name         string `valid:"name" json:"name"`
	City         string `valid:"city" json:"city"`
	Introduction string `valid:"introduction" json:"introduction"`
}

type UserUpdateEmailRequest struct {
	Email      string `json:"email,omitempty" valid:"email"`
	VerifyCode string `json:"verifyCode,omitempty" valid:"verify_code"`
}

type UserUpdatePhoneRequest struct {
	Phone      string `json:"phone,omitempty" valid:"email"`
	VerifyCode string `json:"verifyCode,omitempty" valid:"verify_code"`
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

func UserUpdateEmail(data interface{}, c *gin.Context) map[string][]string {
	currentUser := auth.CurrentUser(c)
	rules := govalidator.MapData{
		"email": []string{
			"required", "min:4",
			"max:30",
			"email",
			"not_exists:users,email," + currentUser.GetStringID(),
			"not_in:" + currentUser.Email,
		},

		"verify_code": []string{"required", "digits:6"},
	}

	messages := govalidator.MapData{
		"name": []string{
			"required:email为必填项",
			"min:email长度需要大于4",
			"max:email长度小于30",
			"not_exists:email已被占用",
			"not_in:email与老email一致",
		},
		"verify_code": []string{
			"required:email为必填项",
			"digits:验证码长度必须为 6 位的数字",
		},
	}
	errs := validate(data, rules, messages)
	_data := data.(*UserUpdateEmailRequest)
	errs = validators.ValidateVerifyCode(_data.Email, _data.VerifyCode, errs)
	return errs
}

func UserUpdatePhone(data interface{}, c *gin.Context) map[string][]string {
	currentUser := auth.CurrentUser(c)
	rules := govalidator.MapData{
		"phone": []string{
			"required",
			"digits:10",
			"not_exists:users,phone," + currentUser.GetStringID(),
			"not_in:" + currentUser.Phone,
		},
		"verify_code": []string{
			"required",
			"digits:6",
		},
	}
	message := govalidator.MapData{
		"phone": []string{
			"required:手机号必须",
			"digits:手机号长度必须为11位的数字",
			"not_exists:手机号已被占用",
			"not_in:新的手机与老手机号一致",
		},
		"verify_code": []string{
			"required:验证码必须",
			"digits:验证码长度必须为6位的数字",
		},
	}
	errs := validate(data, rules, message)
	_data := data.(*UserUpdatePhoneRequest)
	errs = validators.ValidateVerifyCode(_data.Phone, _data.VerifyCode, errs)
	return errs
}
