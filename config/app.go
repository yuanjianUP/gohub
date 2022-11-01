// Package config 站点配置信息
package config

import "gohub/pkg/config"

func init() {
	config.Add("app", func() map[string]interface{} {
		return map[string]interface{}{

			// 应用名称
			"name": config.Env("APP_NAME", "Gohub"),

			// 当前环境，用以区分多环境，一般为 local, stage, production, test
			"env": config.Env("APP_ENV", "production"),

			// 是否进入调试模式
			"debug": config.Env("APP_DEBUG", false),

			// 应用服务端口
			"port": config.Env("APP_PORT", "3000"),

			// 加密会话、JWT 加密
			"key": config.Env("APP_KEY", "33446a9dcf9ea060a0a6532b166da32f304af0de"),

			// 用以生成链接
			"url": config.Env("APP_URL", "http://localhost:3000"),

			// 设置时区，JWT 里会使用，日志记录里也会使用到
			"timezone": config.Env("TIMEZONE", "Asia/Shanghai"),

			//api域名，未设置的话所有api url 加api前缀，加http://domain.com/api/v1/users
			"api_domain": config.Env("API_DOMAIN"),
		}
	})
	config.Add("database", func() map[string]interface{} {
		return map[string]interface{}{

			// 默认数据库
			"connection": config.Env("DB_CONNECTION", "mysql"),

			"mysql": map[string]interface{}{
				"default": map[string]interface{}{
					// 数据库连接信息
					"host":     config.Env("DB_HOST", "127.0.0.1"),
					"port":     config.Env("DB_PORT", "3306"),
					"database": config.Env("DB_DATABASE", "gohub"),
					"username": config.Env("DB_USERNAME", ""),
					"password": config.Env("DB_PASSWORD", ""),
					"charset":  "utf8mb4",
					// 连接池配置
					"max_idle_connections": config.Env("DB_MAX_IDLE_CONNECTIONS", 100),
					"max_open_connections": config.Env("DB_MAX_OPEN_CONNECTIONS", 25),
					"max_life_seconds":     config.Env("DB_MAX_LIFE_SECONDS", 5*60),
				},
			},

			"sqlite": map[string]interface{}{
				"database": config.Env("DB_SQL_FILE", "database/database.db"),
			},
		}
	})
}
