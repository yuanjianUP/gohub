package factories

import (
	"gohub/app/models/user"
	"gohub/pkg/helpers"

	"github.com/bxcodec/faker/v3"
)

//存放工厂方法
func MakeUsers(times int) []user.User {
	var objs []user.User
	//设置唯一值
	faker.SetGenerateUniqueValues(true)
	for i := 0; i < times; i++ {
		model := user.User{
			Name:     faker.Username(),
			Email:    faker.Email(),
			Phone:    helpers.RandomString(11),
			Password: "123456",
		}
		objs = append(objs, model)
	}
	return objs
}
