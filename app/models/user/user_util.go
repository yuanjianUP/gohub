package user

import (
	"gohub/pkg/app"
	"gohub/pkg/database"
	"gohub/pkg/paginator"

	"github.com/gin-gonic/gin"
)

//判断邮箱是否被使用
func IsEmailExist(email string) bool {
	var count int64
	database.DB().Model(User{}).Where("email = ?", email).Count(&count)
	return count > 0
}

//判断手机号已被注册
func IsPhoneExist(phone string) bool {
	var count int64
	database.DB().Model(User{}).Where("phone = ?", phone).Count(&count)
	return count > 0
}

//通过手机号/email/用户名 来获取用户
func GetByMulti(loginID string) (userModel User) {
	database.DB().
		Where("phone = ?", loginID).
		Or("email = ?", loginID).
		Or("name = ?", loginID).
		First(&userModel)
	return
}
func Get(idstr string) (userModel User) {
	database.DB().Where("id", idstr).First(&userModel)
	return
}
func All() (users []User) {
	database.DB().Find(&users)
	return
}
func Paginate(c *gin.Context, perPage int) (users []User, paging paginator.Paging) {
	paging = paginator.Paginate(
		c,
		database.DB().Model(User{}),
		&users,
		app.V1URL(database.TableName(&User{})),
		perPage,
	)
	return
}
