package requests

import (
	"github.com/gin-gonic/gin"
	"github.com/thedevsaddam/govalidator"
	"gohub/app/requests/validators"
)

type SignupPhoneExistRequest struct {
	Phone string `json:"phone,omitempty" valid:"phone"`
}
type SignupEmailExistRequest struct {
	Email string `json:"email"  valid:"email"`
}
type SignupUsingEmailRequest struct {
	Email           string `json:"email,omitempty" valid:"email"`
	VerifyCode      string `json:"verify_code,omitempty" valid:"verify_code"`
	Name            string `json:"name" valid:"name"`
	PassWord        string `json:"password,omitempty" valid:"password"`
	PassWordConfirm string `json:"password_confirm,omitempty" valid:"password_confirm"`
}

func ValidateSignupPhoneExist(data interface{}, c *gin.Context) map[string][]string {

	// 自定义验证规则
	rules := govalidator.MapData{
		"phone": []string{"required", "digits:11"},
	}

	// 自定义验证出错时的提示
	messages := govalidator.MapData{
		"phone": []string{
			"required:手机号为必填项，参数名称 phone",
			"digits:手机号长度必须为 11 位的数字",
		},
	}

	return validate(data, rules, messages)
}
func ValidateSignupEmailExist(data interface{}, c *gin.Context) map[string][]string {

	// 自定义验证规则
	rules := govalidator.MapData{
		"email": []string{"required", "email"},
	}

	// 自定义验证出错时的提示
	messages := govalidator.MapData{
		"email": []string{
			"required:邮箱必须",
			"email:邮箱格式不正确",
		},
	}
	// 开始验证
	return validate(data, rules, messages)
}
func SignupUsingEmail(data interface{}, c *gin.Context) map[string][]string {
	rules := govalidator.MapData{
		"email":            []string{"required", "min:4", "max:30", "email"},
		"name":             []string{"required", "alpha_num", "between:3,20", "not_exists:users,name"},
		"password":         []string{"required", "min:6"},
		"password_confirm": []string{"required"},
		"verify_code":      []string{"required", "digits:6"},
	}
	message := govalidator.MapData{
		"email": []string{
			"required:email 必须填写",
			"min:email 长度需要大于4",
			"max:email 长度小于 30",
			"email:email格式不正确",
			"not_exxists:email已经占用",
		},
		"name": []string{
			"required:name 必须填写",
			"alpha_num:用户格式错误，只允许数字和英文",
			"between:用户名长度为3-20之间",
		},
		"password": []string{
			"required:password 必须",
			"min:密码长度大于6",
		},
		"password_confirm": []string{
			"required:确认密码必须",
		},
		"verify_code": []string{
			"required:验证码答案必须",
			"digits:验证码长度必须为6位的数字",
		},
	}
	errs := validate(data, rules, message)
	_data := data.(*SignupUsingEmailRequest)
	errs = validators.ValidatePasswordConfirm(_data.PassWord, _data.PassWordConfirm, errs)
	errs = validators.ValidateVerifyCode(_data.Email, _data.VerifyCode, errs)
	return errs
}
