<?php
/*
Plugin Name: Convert Footnotes
Plugin URI: http://ben.balter.com/2011/03/20/regular-expression-to-parse-word-style-footnotes/
Description: Converts Word Footnotes to Simple Footnotes format. Requires Simple Footnotes installed, available at: http://wordpress.org/extend/plugins/simple-footnotes/
Version: 0.1
Author: Benjamin J. Balter
Author URI: http://ben.balter.com/
Revised by Marc Chehab: http://www.marcchehab.org/
*/
 
/**
 * Function which uses regular expression to parse Microsoft Word footnotes
 * into WordPress's Simple Footnotes format
 *
 * @param string $content post content from filter hook
 * @returns string post content with parsed footnotes
 * @link http://ben.balter.com/2011/03/20/regular-expression-to-parse-word-style-footnotes/
 */
 
function bb_parse_footnotes( $content ) {
 
    global $post;
    if ( !isset( $post ) )
        return;
 
    //if we have already parsed, kick
    if ( get_post_meta($post->ID, 'parsed_footnotes') )
        return $content;
 
    $content = stripslashes( $content );
 
    //grab all the Word-style footnotes into an array
    $pattern = '/\<a( title\=\"\")? href\=\"[^\"]*\#_ftnref([0-9]+)\"\>\[([0-9]+)\]\<\/a\>(.*)/';
    preg_match_all( $pattern, $content, $footnotes, PREG_SET_ORDER);
 
    //build find and replace arrays
    foreach ($footnotes as $footnote) {
        $find[] = '/\<a( title\=\"\")? href\=\"[^\"]*\#_ftn'.$footnote[2].'\"\>(\<strong\>)?\['.$footnote[2].'\](\<\/strong\>)?\<\/a\>/';
        $replace[] = '[ref]' . str_replace( array("\r\n", "\r", "\n"), "", $footnote[4]) . '[/ref]';
    }
 
    //remove all the original footnotes when done
    $find[] = '/\<div\>\s*(\<p\>)?\<a( title\=\"\")? href\=\"[^\"]*\#_ftnref([0-9]+)\"\>\[([0-9]+)\]\<\/a\>(.*)\s*\<\/div\>\s+/s';
    $replace[] = '';
 
    //make the switch
    $content = preg_replace( $find, $replace, $content );
 
    //add meta so we know it has been parsed
    add_post_meta($post->ID,'parsed_footnotes', true, true);

    return addslashes($content);
}

add_filter( 'content_save_pre', 'bb_parse_footnotes' );