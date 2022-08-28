package console

import (
	"fmt"
	"github.com/mgutz/ansi"
	"os"
)

//打印一条成功消息，绿色输出
func Success(msg string) {
	colorOut(msg, "green")
}

//打印一条错误消息，红色输出
func Error(msg string) {
	colorOut(msg, "red")
}

//warning 打印一条提示消息，黄色输出
func Warning(msg string) {
	colorOut(msg, "yellow")
}

//一条错误消息，并退出
func Exit(msg string) {
	Error(msg)
	os.Exit(1)
}

//自带nil判断
func ExitIf(err error) {
	if err != nil {
		Exit(err.Error())
	}
}

//内部使用，设置高亮颜色
func colorOut(message, color string) {
	fmt.Fprintln(os.Stdout, ansi.Color(message, color))
}
