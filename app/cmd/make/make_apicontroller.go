package make

import (
	"fmt"
	"gohub/pkg/console"
	"strings"

	"github.com/spf13/cobra"
)

var CmdMakeAPIController = &cobra.Command{
	Use:   "apicontroller",
	Short: "Create api controller,exmaple:make appcontroller v1/user",
	Run:   runMakeAPIController,
	Args:  cobra.ExactArgs(1),
}

func runMakeAPIController(cmd *cobra.Command, args []string) {
	//处理参数，要求附带api版本v1或者v2
	array := strings.Split(args[0], "/")
	if len(array) != 2 {
		console.Exit("api controller name format:v1/user")
	}
	//apiversion用来拼接目标路径
	//name 用来生成cmd.model实例
	apiVersion, name := array[0], array[1]
	model := makeModelFromString(name)
	//组建目标目录
	filePath := fmt.Sprintf("app/http/controllers/api/%s/%s_controller.go", apiVersion, model.TableName)
	//基于模版创建文件（做好变量替换）
	createFileFromStub(filePath, "apicontroller", model)
}
