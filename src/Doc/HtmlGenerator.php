<?php

namespace LaravelNemo\Doc;



use LaravelNemo\Interface\IDocGenerator;

class HtmlGenerator implements IDocGenerator
{


    public function __construct(private string $content){

    }

    /**
     * 生成html格式
     */
    public function generate(): FileStore
    {
        $parsedown = new \Parsedown();
        $parsedown->setUrlsLinked(true);
        $body = $parsedown->text($this->content);
        $head = '
            <meta charset="utf-8">
            <style>
                .ztree {
                    width: 400px;
                    top: 0;
                    left: 0;
                    background: #eee;
                    overflow: auto;
                }
                .markdown-body{
                    margin-left: 400px;
                }
            </style>

            <link href="https://cdn.bootcdn.net/ajax/libs/github-markdown-css/4.0.0/github-markdown.css" rel="stylesheet" type="text/css">
            <link rel="stylesheet" href="https://i5ting.github.io/msgpack-specification/toc/css/zTreeStyle/zTreeStyle.css" type="text/css">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/highlight.js/8.4.0/styles/solarized_light.min.css">

            <script src="https://cdn.jsdelivr.net/highlight.js/8.4.0/highlight.min.js"></script>
            <script type="text/javascript" src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
            <script type="text/javascript" src="https://i5ting.github.io/msgpack-specification/toc/js/jquery.ztree.all-3.5.min.js"></script>
            <script type="text/javascript" src="https://i5ting.github.io/msgpack-specification/toc/js/ztree_toc.js"></script>
            <script>
                $().ready(function(){
                   $("#ztree").ztree_toc({
                    is_highlight_selected_line: true
                    });
                   hljs.initHighlightingOnLoad();

                   $(".switch.level2").click();
                })
            </script>

            <div  id=\'ztree\' class=\'ztree\'></div>
        ';
        $html = $head . "<div class='markdown-body'>{$body}</div>";
        return new FileStore($html, 'html');
    }
}
