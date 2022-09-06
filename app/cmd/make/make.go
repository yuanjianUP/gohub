package make

import (
	"embed"
	"fmt"
	"gohub/pkg/console"
	"gohub/pkg/file"
	"gohub/pkg/str"
	"strings"

	"github.com/iancoleman/strcase"
	"github.com/spf13/cobra"
)

type Model struct {
	TableName          string
	StructName         string
	StructNamePlural   string
	VariableName       string
	VariableNamePlural string
	PackageName        string
}

//以下注视必须 有一个目录是”stubs”，并且这个目录可以被访问到。至此完结
//go:embed stubs
var stubsFS embed.FS
var CmdMake = &cobra.Command{
	Use:   "make",
	Short: "Generate file and code",
}

func init() {
	//注册make的子命令
	CmdMake.AddCommand(
		CmdMakeCMD,
	)
}

//格式化用户输入的内容
func makeModelFromString(name string) Model {
	model := Model{}
	model.StructName = str.Singular(strcase.ToCamel(name))
	model.StructNamePlural = str.Plural(model.StructName)
	model.TableName = str.Snake(model.StructNamePlural)
	model.VariableName = str.LowerCamel(model.StructName)
	model.PackageName = str.Snake(model.StructName)
	model.VariableNamePlural = str.LowerCamel(model.StructNamePlural)
	return model
}

func createFileFromStub(filePath string, stubName string, model Model, variables ...interface{}) {
	replaces := make(map[string]string)
	//实现最后一个参数可选
	if len(variables) > 0 {
		replaces = variables[0].(map[string]string)
	}
	//目标文件已存在
	if file.Exists(filePath) {
		console.Exit(filePath + " already exists!")
	}
	//读取stub模版文件
	modelData, err := stubsFS.ReadFile("stubs/" + stubName + ".stub")
	if err != nil {
		console.Exit(err.Error())
	}
	modelStub := string(modelData)
	//添加默认的替换变量
	replaces["{{VariableName}}"] = model.VariableName
	replaces["{{VariableNamePlural}}"] = model.VariableNamePlural
	replaces["{{StructName}}"] = model.StructName
	replaces["{{StructNamePlural}}"] = model.StructNamePlural
	replaces["{{PackageName}}"] = model.PackageName
	replaces["{{TableName}}"] = model.TableName

	// 对模板内容做变量替换
	for search, replace := range replaces {
		modelStub = strings.ReplaceAll(modelStub, search, replace)
	}

	// 存储到目标文件中
	err = file.Put([]byte(modelStub), filePath)
	if err != nil {
		console.Exit(err.Error())
	}

	// 提示成功
	console.Success(fmt.Sprintf("[%s] created.", filePath))
}
