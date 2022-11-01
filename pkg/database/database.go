// Package database 数据库操作
package database

import (
	"database/sql"
	"errors"
	"fmt"
	"gohub/pkg/config"

	"gorm.io/gorm"
	gormlogger "gorm.io/gorm/logger"
)

// DB 对象
type DBInfo struct {
	DB    *gorm.DB
	SQLDB *sql.DB
}

//数据库连接信息
var DBConnections map[string]*DBInfo

// Connect 连接数据库
func Connect(dbConfigs map[string]gorm.Dialector, _logger gormlogger.Interface) {

	// 使用 gorm.Open 连接数据库
	var err error
	var db *gorm.DB
	var sqldb *sql.DB
	DBConnections = make(map[string]*DBInfo, len(dbConfigs))
	for k, dbconfig := range dbConfigs {
		db, err = gorm.Open(dbconfig, &gorm.Config{
			Logger: _logger,
		})
		// 处理错误
		if err != nil {
			fmt.Println(err.Error())
		}

		// 获取底层的 sqlDB
		sqldb, err = db.DB()

		if err != nil {
			fmt.Println(err.Error())
		}

		dbinfo := &DBInfo{
			DB:    db,
			SQLDB: sqldb,
		}

		DBConnections[k] = dbinfo
	}

}

func DB(name ...string) *gorm.DB {
	if len(name) > 0 {
		if collect, ok := DBConnections[name[0]]; ok {
			return collect.DB
		}
		return nil
	}
	return DBConnections["default"].DB
}

func SQLDB(name ...string) *sql.DB {
	if len(name) > 0 {
		if collect, ok := DBConnections[name[0]]; ok {
			return collect.SQLDB
		}
		return nil
	}
	return DBConnections["default"].SQLDB
}

func CurrentDatabase() (dbname string) {
	dbname = DB().Migrator().CurrentDatabase()
	return
}
func DeleteAllTables() error {
	var err error
	switch config.Get("database.connection") {
	case "mysql":
		err = deleteMySQLTables()
	case "sqlite":
		deleteAllSqliteTables()
	default:
		panic(errors.New("database connection not supported"))
	}
	return err
}
func deleteAllSqliteTables() error {
	tables := []string{}
	//读取所有数据表
	err := DB().Select(&tables, "SELECT name FROM sqlite_master WHERE type =`table`").Error
	if err != nil {
		return err
	}
	for _, table := range tables {
		err := DB().Migrator().DropTable(table)
		if err != nil {
			return err
		}
	}
	return nil
}
func deleteMySQLTables() error {
	dbname := CurrentDatabase()
	tables := []string{}
	//读取所有数据表
	err := DB().Table("information_schema.tables").
		Where("table_schema = ?", dbname).
		Pluck("table_name", &tables).
		Error
	if err != nil {
		return err
	}
	//暂时关闭外键值检测
	DB().Exec("SET foreign_key_checks = 0;")
	//删除所有表
	for _, table := range tables {
		err := DB().Migrator().DropTable(table)
		if err != nil {
			return err
		}
	}
	//开启mysql外键检测
	DB().Exec("SET foreign_key_checks = 1;")
	return nil
}

//获取表名称
func TableName(obj interface{}) string {
	stmt := &gorm.Statement{DB: DB()}
	stmt.Parse(obj)
	return stmt.Schema.Table
}
