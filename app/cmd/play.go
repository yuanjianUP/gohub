package cmd

import (
	"github.com/spf13/cobra"
	"gohub/pkg/console"
	"gohub/pkg/redis"
	"time"
)

var CmdPlay = &cobra.Command{
	Use:   "play",
	Short: "likes the go playground,but running at our application context",
	Run:   runPlay,
}

//调试完成后请记得清除测试代码
func runPlay(cmd *cobra.Command, args []string) {
	//存进去 redis中
	redis.Redis.Set("hello", "hi from redis", 10*time.Second)
	//从redis里取出
	console.Success(redis.Redis.Get("hello"))
}
