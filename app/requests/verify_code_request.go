package requests

import (
	"github.com/gin-gonic/gin"
	"github.com/thedevsaddam/govalidator"
	"gohub/app/requests/validators"
)

type VerifyCodeEmailRequest struct {
	CaptchaID     string `json:"captcha_id,omitempty" valid:"captcha_id"`
	CaptchaAnswer string `json:"captcha_answer,omitempty" valid:"captcha_answer"`

	Email string `json:"email,omitempty" valid:"email"`
}

func VerifyCodeEmail(data interface{}, c *gin.Context) map[string][]string {
	rules := govalidator.MapData{
		"email":          []string{"required", "min:4", "max:30", "email"},
		"captcha_id":     []string{"required"},
		"captcha_answer": []string{"required", "digits:6"},
	}
	messages := govalidator.MapData{
		"email": []string{
			"required:email必须填写",
			"min:email长度大于3",
			"max:email长度必须小雨30",
			"email:格式不正确",
		},
		"captcha_id": []string{
			"required:图片验证码的ID为必须",
		},
		"captcha_answer": []string{
			"required:图片验证码必须填写",
			"required:图片验证码长度必须为6为的数字",
		},
	}
	errs := validate(data, rules, messages)
	_data := data.(*VerifyCodeEmailRequest) //将interface断言成verifycodeEmailRequest
	errs = validators.ValidateCaptcha(_data.CaptchaID, _data.CaptchaAnswer, errs)
	return errs
}
