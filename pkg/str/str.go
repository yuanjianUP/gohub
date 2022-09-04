package str

import (
	"github.com/gertd/go-pluralize"
	"github.com/iancoleman/strcase"
)

//转为附属 user->users
func Plural(word string) string {
	return pluralize.NewClient().Plural(word)
}

//转单数
func Singular(word string) string {
	return pluralize.NewClient().Singular(word)
}

//转为snake_case如TopicComment->topic_comment
func Snake(s string) string {
	return strcase.ToSnake(s)
}

//topic_comment->TopicComment
func Camel(s string) string {
	return strcase.ToCamel(s)
}

//lowerCamel转lowerCamelCase,如 TopicComment -> topicComment
func LowerCamel(s string) string {
	return strcase.ToLowerCamel(s)
}
