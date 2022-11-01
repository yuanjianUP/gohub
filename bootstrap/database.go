package bootstrap

import (
	"errors"
	"fmt"
	"gohub/pkg/config"
	"gohub/pkg/database"
	"gohub/pkg/logger"
	"time"

	"gorm.io/driver/mysql"
	"gorm.io/driver/sqlite"
	"gorm.io/gorm"
)

// SetupDB 初始化数据库和 ORM
func SetupDB() {

	var dbConfigs map[string]gorm.Dialector
	switch config.Get("database.connection") {
	case "mysql":
		connections := config.GetStringMapString("database.mysql")
		for name := range connections {
			// 构建 DSN 信息
			dsn := fmt.Sprintf("%v:%v@tcp(%v:%v)/%v?charset=%v&parseTime=True&multiStatements=true&loc=Local",
				config.Get("database.mysql."+name+".username"),
				config.Get("database.mysql."+name+".password"),
				config.Get("database.mysql."+name+".host"),
				config.Get("database.mysql."+name+".port"),
				config.Get("database.mysql."+name+".database"),
				config.Get("database.mysql."+name+".charset"),
			)
			if dbConfigs == nil {
				dbConfigs = make(map[string]gorm.Dialector, len(connections))
			}
			dbConfigs[name] = mysql.New(mysql.Config{
				DSN: dsn,
			})
		}
	case "sqlite":
		connections := config.GetStringMapString("database.sqlite")
		if dbConfigs == nil {
			dbConfigs = make(map[string]gorm.Dialector, len(connections))
		}
		for name := range connections {
			// 初始化 sqlite
			database := config.Get("database.sqlite." + name + ".database")
			dbConfigs[name] = sqlite.Open(database)
		}

	default:
		panic(errors.New("database connection not supported"))
	}

	// 连接数据库，并设置 GORM 的日志模式
	database.Connect(dbConfigs, logger.NewGormLogger())

	for _, dbinfo := range database.DBConnections {
		sqldb := dbinfo.SQLDB
		// 设置最大连接数
		sqldb.SetMaxOpenConns(config.GetInt("database.mysql.max_open_connections"))
		// 设置最大空闲连接数
		sqldb.SetMaxIdleConns(config.GetInt("database.mysql.max_idle_connections"))
		// 设置每个链接的过期时间
		sqldb.SetConnMaxLifetime(time.Duration(config.GetInt("database.mysql.max_life_seconds")) * time.Second)
	}

	//database.DB.AutoMigrate(&user.User{}) //初始化表
}
