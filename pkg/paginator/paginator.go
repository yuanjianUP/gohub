package paginator

import (
	"fmt"
	"gohub/pkg/config"
	"gohub/pkg/logger"
	"math"
	"strings"

	"github.com/gin-gonic/gin"
	"github.com/spf13/cast"
	"gorm.io/gorm"
	"gorm.io/gorm/clause"
)

//paging分页数据
type Paging struct {
	CurrentPage int    //当前页
	PerpPage    int    //每页条数
	TotalPage   int    //总页数
	TotalCount  int64  //总条数
	NextPageURL string //下一页的链接
	PrevPageURL string //上一页的链接
}

//paginator分页操作类
type Paginator struct {
	BaseURL    string //用以拼接url
	PerPage    int    //每页条数
	Page       int    //当前页
	Offset     int    //数据库读取数据库时offset的值
	TotalCount int64  //总条数
	TotalPage  int    //总页数 = TotalCount/PerPage
	Sort       string //排序规则
	Order      string //排序顺序

	query *gorm.DB     //db query 句柄
	ctx   *gin.Context //gin context,方便调用
}

func Paginate(c *gin.Context, db *gorm.DB, data interface{}, baseURL string, perPage int) Paging {
	//初始化paginator实例
	p := &Paginator{
		query: db,
		ctx:   c,
	}
	p.initProperties(perPage, baseURL)
	//查询数据库
	err := p.query.Preload(clause.Associations).
		Order(p.Sort + " " + p.Order).
		Limit(p.PerPage).
		Offset(p.Offset).
		Find(data).
		Error
	if err != nil {
		logger.LogIf(err)
		return Paging{}
	}
	return Paging{
		CurrentPage: p.Page,
		PerpPage:    p.PerPage,
		TotalPage:   p.TotalPage,
		TotalCount:  p.TotalCount,
		NextPageURL: p.getNextPageURL(),
		PrevPageURL: p.getPrevPageURL(),
	}
}

//初始化分页必须用到的属性，基于这些属性查询数据库
func (p *Paginator) initProperties(perPage int, baseURL string) {
	p.BaseURL = p.formatBaseURL(baseURL)
	p.PerPage = p.getPerPage(perPage)
	//排序参数
	p.Order = p.ctx.DefaultQuery(config.Get("paging.url_query_order"), "asc")
	p.Sort = p.ctx.DefaultQuery(config.Get("paging.url_query_sort"), "id")

	p.TotalCount = p.getTotalCount()
	p.TotalPage = p.getTotalPage()
	p.Page = p.getCurrentPage()
	p.Offset = (p.Page - 1) * p.PerPage
}
func (p Paginator) getPerPage(perPage int) int {
	//优先使用请求 per_page参数
	queryPerpage := p.ctx.Query(config.Get("paging.url_query_per_page"))
	if len(queryPerpage) > 0 {
		perPage = cast.ToInt(queryPerpage)
	}
	//没有传参，使用默认
	if perPage <= 0 {
		perPage = config.GetInt("paging.perpage")
	}
	return perPage
}

//返回当前页
func (p Paginator) getCurrentPage() int {
	//优先获取用户请求的page
	page := cast.ToInt(p.ctx.Query(config.Get("paging.url_query_page")))
	if page <= 0 {
		return 0
	}
	if p.TotalPage == 0 {
		return 0
	}
	if page > p.TotalPage {
		return p.TotalPage
	}
	return page
}

//返回数据库里条数
func (p *Paginator) getTotalCount() int64 {
	var count int64
	if err := p.query.Count(&count).Error; err != nil {
		return 0
	}
	return count
}

//计算总页数
func (p Paginator) getTotalPage() int {
	if p.TotalCount == 0 {
		return 0
	}
	nums := int64(math.Ceil(float64(p.TotalCount) / float64(p.PerPage)))
	if nums == 0 {
		nums = 1
	}
	return int(nums)
}
func (p *Paginator) formatBaseURL(baseURL string) string {
	if strings.Contains(baseURL, "?") {
		baseURL = baseURL + "&" + config.Get("paging.url_query_page") + "="
	} else {
		baseURL = baseURL + "?" + config.Get("paging.url_query_page") + "="
	}
	return baseURL
}

//拼接分页链接
func (p Paginator) getPageLink(page int) string {
	return fmt.Sprintf("%v%v&%s=%s&%s=%s&%s=%v",
		p.BaseURL,
		page,
		config.Get("paging.url_query_sort"),
		p.Sort,
		config.Get("paging.url_query_order"),
		p.Order,
		config.Get("paging.url_query_per_page"),
		p.PerPage,
	)
}

//返回下一页链接
func (p Paginator) getNextPageURL() string {
	if p.TotalPage > p.Page {
		return p.getPageLink(p.Page + 1)
	}
	return ""
}
func (p Paginator) getPrevPageURL() string {
	if p.Page <= 1 || p.Page > p.TotalPage {
		return ""
	}
	return p.getPageLink(p.Page - 1)
}
