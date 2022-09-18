package make

import (
	"fmt"

	"github.com/spf13/cobra"
)

var CmdMakeFactory = &cobra.Command{
	Use:   "factory",
	Short: "create model's factory file, example:make factory user",
	Run:   runMakeFactory,
	Args:  cobra.ExactArgs(1),
}

func runMakeFactory(cmd *cobra.Command, args []string) {
	//格式化模型名称，返回一个model对象
	model := makeModelFromString(args[0])
	//拼接目标文件路径
	filePath := fmt.Sprintf("database/factories/%s_factory.go", model.PackageName)
	//基于模版创建文件（做好变量替换）
	createFileFromStub(filePath, "factory", model)
}
