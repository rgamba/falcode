<?php
/***************************************************************************
 *                              -------------------
 *     begin                : Thu May 31, 2001
 *     copyright            : (C) 2001 The phpBB Group
 *     email                : support@phpbb.com
 *
 *     $Id: sql_parse.php,v 1.8 2002/03/18 23:53:12 psotfx Exp $
 *
 ****************************************************************************/
function remove_comments(&$output)
{
    $lines = explode("\n", $output);
    $output = "";

    // try to keep mem. use down
    $linecount = count($lines);

    $in_comment = false;
    for($i = 0; $i < $linecount; $i++)
    {
        if( preg_match("/^\/\*/", preg_quote($lines[$i])) )
        {
            $in_comment = true;
        }

        if( !$in_comment )
        {
            $output .= $lines[$i] . "\n";
        }

        if( preg_match("/\*\/$/", preg_quote($lines[$i])) )
        {
            $in_comment = false;
        }
    }

    unset($lines);
    return $output;
}

//
// remove_remarks will strip the sql comment lines out of an uploaded sql file
//
function remove_remarks($sql)
{
    $lines = explode("\n", $sql);

    // try to keep mem. use down
    $sql = "";

    $linecount = count($lines);
    $output = "";

    for ($i = 0; $i < $linecount; $i++)
    {
        if (($i != ($linecount - 1)) || (strlen($lines[$i]) > 0))
        {
            if (isset($lines[$i][0]) && $lines[$i][0] != "#")
            {
                $output .= $lines[$i] . "\n";
            }
            else
            {
                $output .= "\n";
            }
            // Trading a bit of speed for lower mem. use here.
            $lines[$i] = "";
        }
    }

    return $output;

}

//
// split_sql_file will split an uploaded sql file into single sql statements.
// Note: expects trim() to have already been run on $sql.
//
function split_sql_file($sql, $delimiter)
{
    // Split up our string into "possible" SQL statements.
    $tokens = explode($delimiter, $sql);

    // try to save mem.
    $sql = "";
    $output = array();

    // we don't actually care about the matches preg gives us.
    $matches = array();

    // this is faster than calling count($oktens) every time thru the loop.
    $token_count = count($tokens);
    for ($i = 0; $i < $token_count; $i++)
    {
        // Don't wanna add an empty string as the last thing in the array.
        if (($i != ($token_count - 1)) || (strlen($tokens[$i] > 0)))
        {
            // This is the total number of single quotes in the token.
            $total_quotes = preg_match_all("/'/", $tokens[$i], $matches);
            // Counts single quotes that are preceded by an odd number of backslashes,
            // which means they're escaped quotes.
            $escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$i], $matches);

            $unescaped_quotes = $total_quotes - $escaped_quotes;

            // If the number of unescaped quotes is even, then the delimiter did NOT occur inside a string literal.
            if (($unescaped_quotes % 2) == 0)
            {
                // It's a complete sql statement.
                $output[] = $tokens[$i];
                // save memory.
                $tokens[$i] = "";
            }
            else
            {
                // incomplete sql statement. keep adding tokens until we have a complete one.
                // $temp will hold what we have so far.
                $temp = $tokens[$i] . $delimiter;
                // save memory..
                $tokens[$i] = "";

                // Do we have a complete statement yet?
                $complete_stmt = false;

                for ($j = $i + 1; (!$complete_stmt && ($j < $token_count)); $j++)
                {
                    // This is the total number of single quotes in the token.
                    $total_quotes = preg_match_all("/'/", $tokens[$j], $matches);
                    // Counts single quotes that are preceded by an odd number of backslashes,
                    // which means they're escaped quotes.
                    $escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$j], $matches);

                    $unescaped_quotes = $total_quotes - $escaped_quotes;

                    if (($unescaped_quotes % 2) == 1)
                    {
                        // odd number of unescaped quotes. In combination with the previous incomplete
                        // statement(s), we now have a complete statement. (2 odds always make an even)
                        $output[] = $temp . $tokens[$j];

                        // save memory.
                        $tokens[$j] = "";
                        $temp = "";

                        // exit the loop.
                        $complete_stmt = true;
                        // make sure the outer loop continues at the right point.
                        $i = $j;
                    }
                    else
                    {
                        // even number of unescaped quotes. We still don't have a complete statement.
                        // (1 odd and 1 even always make an odd)
                        $temp .= $tokens[$j] . $delimiter;
                        // save memory.
                        $tokens[$j] = "";
                    }

                } // for..
            } // else
        }
    }

    return $output;
}

function execute_sql_file($file,$host=NULL,$user=NULL,$pass=NULL,$db=NULL){
    $sql_query = @fread(@fopen($file, 'r'), @filesize($file)) or die('Problem reading the SQL file');
    $sql_query = remove_remarks($sql_query);
    $sql_query = split_sql_file($sql_query, ';');

    if($host != NULL){
        $con = mysql_connect($host,$user,$pass) or die('Couldn\'t connecto to MySQL with the specified data');
        mysql_select_db($db) or die('Error selecting the database');
    }

    $error = false;
    $error_msg = "";

    $i=1;
    foreach($sql_query as $sql){
        $qry = mysql_query($sql);
        if(mysql_affected_rows($qry) === false || $qry === false){
            $error = true;
            $error_msg = mysql_error();
            break;
        }
        /*echo $sql."<hr>";
        $i++;
        if($i>=50)
            break;*/

    }

    if($error == true){
        return $error_msg;
    }else{
        return true;
    }
}