package cache

import "time"

type Store interface {
	Set(key string, value string, expireTime time.Duration)
	Get(key string) string
	Has(key string) bool
	Forget(key string)
	forever(key string, value string)
	Flush()
	IsAlive() error

	//参数只有1个时，为key，增加1.
	//当参数有2个时，第一个参数为key，第二个参数为要减去的值Int64类型
	Increment(parameters ...interface{})

	//当参数只有1个小时，为key减去1
	//当参数有两个时，第一个为参数key，第二个为减去的值
	Decrement(parameters ...interface{})
}
