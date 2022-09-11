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

func init() {
	CmdMigrate.AddCommand(
		CmdMigrateUp,
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
