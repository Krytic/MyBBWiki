<?php

namespace ashtaev;

/**
 * Table of contents generation library.
 *
 * @author Vladislav Ashtaev <vl.ashtaev@gmail.com>
 * @see https://github.com/ashtaev/php-table-of-contents
 */
class Toc {

    private $content;
    private $count;
    private $title;
    private $level;
    private $text;
    private $place = 'title';
    private $shortcode = "#<!--\[toc\]-->#is";


    /**
     * @param string $content A post includes the headers.
     */
    public function __construct($content) {

        preg_match_all("#<[hH]([1-6])>(.*?)</[hH][1-6]>#is", $content, $match);

        $this->content = $content;
        $this->count   = count($match[0]);
        $this->title   = $match[0];
        $this->level   = $match[1];
        $this->text    = $match[2];
    }


    /**
     * Creates an array of table of contents data structure.
     *
     * @return array
     */
    public function getDataToc() {

        $toc = [];

        $count = $this->count;
        $level = $this->level;
        $text  = $this->text;


        for ($i=0, $j=0; $i<$count; $i++, $j++) {

            // the first header
            if ($i === 0) {
                $toc[$i]['list_open']  = true;
                $toc[$i]['item_open']  = true;
                $toc[$i]['text']       = $text[$i];
                $toc[$i]['href']       = $this->getHref($text[$i]);
                $toc[$i]['item_close'] = $level[$i] == $level[$i+1] or (!isset($level[$i+1]));
                $toc[$i]['list_close'] = $level[$i] >  $level[$i+1] or (!isset($level[$i+1]));

                continue;
            }

            // the last header
            if ($i == $count-1) {
                $toc[$j]['list_open']  = $level[$i] > $level[$i-1];
                $toc[$j]['item_open']  = true;
                $toc[$j]['text']       = $text[$i];
                $toc[$j]['href']       = $this->getHref($text[$i]);
                $toc[$j]['item_close'] = true;
                $toc[$j]['list_close'] = $level[$i] > $level[$i-1];

                ++$j;
                $toc[$j]['list_open']  = false;
                $toc[$j]['item_open']  = false;
                $toc[$j]['text']       = "";
                $toc[$j]['href']       = "";
                $toc[$j]['item_close'] = true;
                $toc[$j]['list_close'] = true;

                break;
            }

            //other header
            $toc[$j]['list_open']  = $level[$i] > $level[$i-1];
            $toc[$j]['item_open']  = true;
            $toc[$j]['text']       = $text[$i];
            $toc[$j]['href']       = $this->getHref($text[$i]);
            $toc[$j]['item_close'] = $level[$i] >= $level[$i+1];
            $toc[$j]['list_close'] = $level[$i] >  $level[$i+1];

            if ($level[$i] > $level[$i+1]) {
                ++$j;
                $toc[$j]['list_open']  = false;
                $toc[$j]['item_open']  = false;
                $toc[$j]['text']       = "";
                $toc[$j]['href']       = "";
                $toc[$j]['item_close'] = true;
                $toc[$j]['list_close'] = false;
            }
        }

        return $toc;
    }


    /**
     * Define the location of the table of contents in the article.
     *
     * @param string $place A post includes the headers.
     * It can take one of three values: "top", "title" or "shortcode".
     * Top: places the table of contents at the beginning of the article.
     * Title: places the table of contents before the first heading [default].
     * Shortcode: places the table of contents at the label location.
     */
    public function setPlace($place) {

        $this->place = $place;
    }


    /**
     * Define the shortcode in the post.
     *
     * @param string $shortcode A post includes the headers.
     */
    public function setShortcode($shortcode) {

        $this->shortcode = "#" . preg_quote($shortcode) . "#is";
    }


    /**
     * Get a modified post.
     *
     * @return string
     */
    public function getPost() {

        $content = $this->content;
        $count   = $this->count;
        $level   = $this->level;
        $title   = $this->title;
        $text    = $this->text;

        for ($i=0; $i<$count; $i++) {

            $tag_id = $this->getTagId($text[$i]);

            $new_title = "<h{$level[$i]}><span id=\"{$tag_id}\">"
                       . $text[$i]
                       . "</span></h{$level[$i]}>";

            $content = str_replace($title[$i],
                                   $new_title,
                                   $content);
        }

        return $content;
    }


    /**
     * Get a generated table of contents.
     *
     * @return string
     */
    public function getToc() {
        ob_start();
        $dataToc = $this->getDataToc();
        include "template.php";
        $toc = ob_get_clean();

        return $toc;
    }


    /**
     * Get a modified post with a generated table of contents.
     * By default, the table of contents is placed before the first header.
     *
     * @return string
     */
    public function getPostWithToc() {
        switch ($this->place) {
            case "top":
                return $this->getToc() . $this->getPost();
            case "title":
                return preg_replace('#(?=<h\d*\s*)#is',
                                     $this->getToc(),
                                     $this->getPost(), 1);
            case "shortcode":
                return preg_replace($this->shortcode,
                                    $this->getToc(),
                                    $this->getPost(), 1);
        }
    }


    /**
     * @access protected
     * @return string
     */
    protected function getHref($str) {
        $str = str_replace('<br />', '', $str);
        return "#" . str_replace(' ', '_', $str);
    }


    /**
     * @access protected
     * @return string
     */
    protected function getTagId($str) {
        $str = str_replace('<br />', '', $str);
        return str_replace(' ', '_', $str);
    }
}