//处理数据库填充相关逻辑
package seed

import (
	"gohub/pkg/console"
	"gohub/pkg/database"

	"gorm.io/gorm"
)

//存放所有seeder
var seeders []Seeder

//按顺序执行seeder数组
//支持一些必须按顺序执行的seeder,例如topic创建
//必须依赖user，所以topicseeder应该在userseeder后执行
var orderedSeederNames []string

type SeederFunc func(*gorm.DB)

//seeder对应每一个database/seeders目录下的seeder文件
type Seeder struct {
	Func SeederFunc
	Name string
}

//add 注册到seeders数组中
func Add(name string, fn SeederFunc) {
	seeders = append(seeders, Seeder{
		Func: fn,
		Name: name,
	})
}

//setrunorder设置按顺序执行的seeder数组
func SetRunOrder(names []string) {
	orderedSeederNames = names
}

//getseeder通过名称获取seeder对象
func GetSeeder(name string) Seeder {
	for _, sdr := range seeders {
		if name == sdr.Name {
			return sdr
		}
	}
	return Seeder{}
}

//runall运行所有seeder
func RunAll() {
	//先运行ordered
	executed := make(map[string]string)
	for _, name := range orderedSeederNames {
		sdr := GetSeeder(name)
		if len(sdr.Name) > 0 {
			console.Warning("Running ordered seeder:" + sdr.Name)
			sdr.Func(database.DB)
			executed[name] = name
		}
	}
	//在运行剩下的
	for _, sdr := range seeders {
		//过滤已运行
		if _, ok := executed[sdr.Name]; !ok {
			console.Warning("running seeder: " + sdr.Name)
			sdr.Func(database.DB)
		}
	}
}

//运行耽搁seeder
func RunSeeder(name string) {
	for _, sdr := range seeders {
		if name == sdr.Name {
			sdr.Func(database.DB)
			break
		}
	}
}
