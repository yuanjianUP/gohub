package make

import (
	"fmt"
	"os"

	"github.com/spf13/cobra"
)

var CmdMakeModel = &cobra.Command{
	Use:   "model",
	Short: "Crete model file,example:make model user",
	Run:   runMakeModel,
	Args:  cobra.ExactArgs(1), //只允许且必须传1
}

func runMakeModel(cmd *cobra.Command, args []string) {
	//模式化模型名称，返回一个model对象
	model := makeModelFromString(args[0])
	//确保模型的目录存在，例如app/models/user
	dir := fmt.Sprintf("app/models/%s/", model.PackageName)
	//确保父目录和子目录都会创建，第二个参数是目录权限，使用0777
	os.MkdirAll(dir, os.ModePerm)
	//替换变量
	createFileFromStub(dir+model.PackageName+"_model.go", "model/model", model)
	createFileFromStub(dir+model.PackageName+"_util.go", "model/model_util", model)
	createFileFromStub(dir+model.PackageName+"_hooks.go", "model/model_hooks", model)
}
