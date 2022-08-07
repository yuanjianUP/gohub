package response

import (
	"github.com/gin-gonic/gin"
	"gohub/pkg/logger"
	"gorm.io/gorm"
	"net/http"
)

func JSON(c *gin.Context, data interface{}) {
	c.JSON(http.StatusOK, data)
}

func Success(c *gin.Context) {
	JSON(c, gin.H{
		"success": true,
		"message": "操作成功",
	})
}

//成功返回data
func Data(c *gin.Context, data interface{}) {
	JSON(c, gin.H{
		"success": true,
		"data":    data,
	})
}

//执行更新操作,成功后调用
func Created(c *gin.Context, data interface{}) {
	c.JSON(http.StatusCreated, gin.H{
		"success": true,
		"data":    data,
	})
}

//响应201和json数据
func CreatedJSON(c *gin.Context, data interface{}) {
	c.JSON(http.StatusCreated, data)
}

//404响应，未传参msg时使用默认消息
func Abort404(c *gin.Context, msg ...string) {
	c.AbortWithStatusJSON(http.StatusNotFound, gin.H{
		"message": defaultMessage("权限不足，请确认您有对应的权限", msg...),
	})
}

//403未传参msg时使用默认消息
func Abort403(c *gin.Context, msg ...string) {
	c.AbortWithStatusJSON(http.StatusForbidden, gin.H{
		"message": defaultMessage("权限不足，请确认您有对应的权限", msg...),
	})
}

//500响应未传参msg时使用默认消息
func Abort500(c *gin.Context, msg ...string) {
	c.AbortWithStatusJSON(http.StatusInternalServerError, gin.H{
		"message": defaultMessage("权限不足，请确认您有对应的权限", msg...),
	})
}

//响应400，传参err对象，未传参msg时使用默认消息
func BadRequest(c *gin.Context, err error, msg ...string) {
	logger.LogIf(err)
	c.AbortWithStatusJSON(http.StatusBadRequest, gin.H{
		"message": defaultMessage("请求解析错误，请确认请求格式是否正确。上传文件请使用mutipart表头，请参考使用json格式", msg...),
		"error":   err.Error(),
	})
}

//error响应404或422，未传参msg时使用默认消息
func Error(c *gin.Context, err error, msg ...string) {
	logger.LogIf(err)
	//error类型数据库未找到内容
	if err == gorm.ErrRecordNotFound {
		Abort404(c)
		return
	}
	c.AbortWithStatusJSON(http.StatusUnprocessableEntity, gin.H{
		"message": defaultMessage("请求处理失败，请查看error的值", msg...),
		"error":   err.Error(),
	})
}

//处理表单验证不通过的错误，返回json实例
func ValidationError(c *gin.Context, errors map[string][]string) {
	c.AbortWithStatusJSON(http.StatusUnprocessableEntity, gin.H{
		"message": "请求验证不通过，具体请查看errors",
		"errors":  errors,
	})
}

//登陆失败，jwt解析失败调用
func Unauthorized(c *gin.Context, msg ...string) {
	c.AbortWithStatusJSON(http.StatusUnauthorized, gin.H{
		"message": defaultMessage("请求解析错误，请确认请求格式是否正确。上传文件请使用 multipart 标头，参数请使用 JSON 格式。", msg...),
	})
}
func defaultMessage(defaultMsg string, msg ...string) (message string) {
	if len(msg) > 0 {
		message = msg[0]
	} else {
		message = defaultMsg
	}
	return
}
