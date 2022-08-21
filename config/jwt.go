package config

import "gohub/pkg/config"

func init() {
	config.Add("jwt", func() map[string]interface{} {
		return map[string]interface{}{
			//过期时间，单位是分钟，一般不超过两个小时
			"expire_time": config.Env("JWT_EXPIRE_TIME", 120),
			//运行刷新时间，单位分钟，86400为两个月，从token的签名时间算起
			"max_refresh_time": config.Env("JWT_MAX_REFRESH_TIME", 86400),
			//模式下的过期时间，方便本地开发时间
			"debug_expire_time": 86400,
		}
	})
}
