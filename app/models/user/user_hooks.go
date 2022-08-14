package user

import (
	"gohub/pkg/hash"
	"gorm.io/gorm"
)

//gorm的模型钩子，在创建和更新模型钱调用
func (userModel *User) BeforeSave(tx *gorm.DB) (err error) {
	if !hash.BcryptIsHashed(userModel.Password) {
		userModel.Password = hash.BcryptHash(userModel.Password)
	}
	return
}
