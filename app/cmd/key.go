package cmd

import (
	"github.com/spf13/cobra"
	"gohub/pkg/console"
	"gohub/pkg/helpers"
)

var Cmdkey = &cobra.Command{
	Use:   "key",
	Short: "generate app key,will print the generated key",
	Run:   runkeyGenerate,
	Args:  cobra.NoArgs, //不允许传参
}

func runkeyGenerate(cmd *cobra.Command, args []string) {
	console.Success("--")
	console.Success("App Key:")
	console.Success(helpers.RandomString(32))
	console.Success("---")
	console.Warning("please go to .env file to change the app_key option")
}
