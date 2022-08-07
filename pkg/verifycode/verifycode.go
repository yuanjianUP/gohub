package verifycode

import (
	"fmt"
	"gohub/pkg/app"
	"gohub/pkg/config"
	"gohub/pkg/helpers"
	"gohub/pkg/logger"
	"gohub/pkg/mail"
	"gohub/pkg/redis"
	"strings"
	"sync"
)

type VerifyCode struct {
	Store Store
}

var once sync.Once
var internalVerifyCode *VerifyCode

//NewVerifyCode单利模式获取
func NewVerifyCode() *VerifyCode {
	once.Do(func() {
		internalVerifyCode = &VerifyCode{
			Store: &RedisStore{
				RedisClient: redis.Redis,
				KeyPrefix:   config.GetString("app.name" + ":verifycode:"),
			},
		}
	})
	return internalVerifyCode
}

//生成验证码，并放置于reids中
func (vc *VerifyCode) generateVerifyCode(key string) string {
	//生成随机码
	code := helpers.Randomnumber(config.GetInt("verifycode.code_length"))
	//本地环境使用固定验证码
	if app.IsLocal() {
		code = config.GetString("verifycode.debug_code")
	}
	logger.DebugJSON("验证码", "生成验证码", map[string]string{key: code})
	//将验证码级key存放到redis中并设置过期时间
	vc.Store.Set(key, code)
	return code
}

//发送邮件
func (vc *VerifyCode) SendEmail(email string) error {
	//生成验证码
	code := vc.generateVerifyCode(email)
	//方便本地api自动测试
	if !app.IsProduction() && strings.HasSuffix(email, config.GetString("verifycode.debug_email_suffix")) {
		return nil
	}
	content := fmt.Sprintf("<h1>您的 Email 验证码是 %v </h1>", code)
	//发送邮件
	mail.NewMailer().Send(mail.Email{
		From: mail.From{
			Address: config.GetString("mail.from.address"),
			Name:    config.GetString("mail.from.name"),
		},
		To:      []string{email},
		Subject: "email验证码",
		HTML:    []byte(content),
	})
	return nil
}
func (vc *VerifyCode) CheckAnswer(key string, answer string) bool {
	logger.DebugJSON("验证码", "检查验证码", map[string]string{key: answer})
	if !app.IsProduction() &&
		(strings.HasPrefix(key, config.GetString("verfycode.debug_phone_prefix")) ||
			strings.HasPrefix(key, config.GetString("verifycode.debug_phone_prefix"))) {
		return true
	}
	return vc.Store.Verify(key, answer, false)
}
