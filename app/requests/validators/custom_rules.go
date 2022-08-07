package validators

import (
	"errors"
	"fmt"
	"github.com/thedevsaddam/govalidator"
	"gohub/pkg/database"
	"strings"
)

func init() {
	// 自定义规则 not_exists，验证请求数据必须不存在于数据库中。
	// 常用于保证数据库某个字段的值唯一，如用户名、邮箱、手机号、或者分类的名称。
	// not_exists 参数可以有两种，一种是 2 个参数，一种是 3 个参数：
	// not_exists:users,email 检查数据库表里是否存在同一条信息
	// not_exists:users,email,32 排除用户掉 id 为 32 的用户
	govalidator.AddCustomRule("not_exists", func(field string, rule string, message string, value interface{}) error {
		rng := strings.Split(strings.TrimPrefix(rule, "not_exists:"), ".")
		//第一参数，标名称如users
		tableName := rng[0]
		//第二个参数，字段名称,如email或者phone
		dbFiled := rng[1]
		//第三个参数,排除id
		var exceptID string
		if len(rng) > 2 {
			exceptID = rng[2]
		}
		//用户请求过来的数据
		requestValue := value.(string)
		//拼接sql
		query := database.DB.Table(tableName).Where(dbFiled+" = ?", requestValue)
		//如果传参第三个参数,加上sql where过滤
		if len(exceptID) > 0 {
			query.Where("id != ?", exceptID)
		}
		//查询数据库
		var count int64
		query.Count(&count)
		//验证不通过，数据库能找到对应的数据
		if count != 0 {
			//如果有自定义错误消息的话
			if message != "" {
				return errors.New(message)
			}
			//默认错误消息
			return fmt.Errorf("%v 已被占用", requestValue)
		}
		return nil
	})
}
