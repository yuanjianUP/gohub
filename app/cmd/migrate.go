package cmd

import (
	"gohub/database/migrations"
	"gohub/pkg/migrate"

	"github.com/spf13/cobra"
)

var CmdMigrate = &cobra.Command{
	Use:   "migrate",
	Short: "Run database migration",
	//所有migrate下的子命令都会执行一下代码
}

var CmdMigrateUp = &cobra.Command{
	Use:   "up",
	Short: "run unmigrated migrations",
	Run:   runUp,
}
var CmdMigrateRollback = &cobra.Command{
	Use: "down",
	//设置别名migrate down == migrate rollback
	Aliases: []string{"rollback"},
	Short:   "reverse the up command",
	Run:     runDown,
}
var CmdMigrateRest = &cobra.Command{
	Use:   "reset",
	Short: "rollback all database migrations",
	Run:   runReset,
}
var CmdMigrateRefresh = &cobra.Command{
	Use:   "refresh",
	Short: "reset and re-run all migrations",
	Run:   runRefresh,
}

func init() {
	CmdMigrate.AddCommand(
		CmdMigrateUp,
		CmdMigrateRollback,
		CmdMigrateRefresh,
		CmdMigrateRest,
	)
}
func migrator() *migrate.Migrator {
	//注册database/migrations下所有迁移文件
	migrations.Initialize()
	//初始化migrator
	return migrate.NewMigrator()
}
func runUp(cmd *cobra.Command, args []string) {
	migrator().Up()
}
func runDown(cmd *cobra.Command, args []string) {
	migrator().Rollback()
}
func runReset(cmd *cobra.Command, args []string) {
	migrator().Reset()
}
func runRefresh(cmd *cobra.Command, args []string) {
	migrator().Refresh()
}
