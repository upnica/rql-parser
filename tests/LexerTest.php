<?php
namespace Mrix\Rql\ParserTests;

use Mrix\Rql\Parser\Lexer;
use Mrix\Rql\Parser\Token;

/**
 * @covers Lexer
 */
class LexerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $rql
     * @param array $expected
     * @return void
     *
     * @covers Lexer::tokenize()
     * @dataProvider dataTokenize()
     */
    public function testTokenize($rql, $expected)
    {
        $lexer = new Lexer();
        $stream = $lexer->tokenize($rql);

        foreach ($expected as $token) {
            list($value, $type) = $token;

            $this->assertSame($value, $stream->getCurrent()->getValue());
            $this->assertSame($type, $stream->getCurrent()->getType());

            $stream->next();
        }
    }

    /**
     * @return array
     */
    public function dataTokenize()
    {
        return [
            'primitives' => [
                'eq(&eq&limit(limit,)date:empty(),null,1,+1,-1,0,1.5,-.4e12,2015-04-19,2015-04-16T17:40:32Z',
                [
                    ['eq', Token::T_OPERATOR],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['&', Token::T_AMPERSAND],
                    ['eq', Token::T_STRING],
                    ['&', Token::T_AMPERSAND],
                    ['limit', Token::T_OPERATOR],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['limit', Token::T_STRING],
                    [',', Token::T_COMMA],
                    [')', Token::T_CLOSE_PARENTHESIS],
                    ['date', Token::T_TYPE],
                    ['empty()', Token::T_EMPTY],
                    [',', Token::T_COMMA],
                    ['null', Token::T_NULL],
                    [',', Token::T_COMMA],
                    ['1', Token::T_INTEGER],
                    [',', Token::T_COMMA],
                    ['+1', Token::T_INTEGER],
                    [',', Token::T_COMMA],
                    ['-1', Token::T_INTEGER],
                    [',', Token::T_COMMA],
                    ['0', Token::T_INTEGER],
                    [',', Token::T_COMMA],
                    ['1.5', Token::T_FLOAT],
                    [',', Token::T_COMMA],
                    ['-.4e12', Token::T_FLOAT],
                    [',', Token::T_COMMA],
                    ['2015-04-19', Token::T_DATE],
                    [',', Token::T_COMMA],
                    ['2015-04-16T17:40:32Z', Token::T_DATE],
                ],
            ],

            'date support' => [
                'in(a,(2015-04-19,2012-02-29,2015-02-29,2015-13-19))',
                [
                    ['in', Token::T_OPERATOR],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['a', Token::T_STRING],
                    [',', Token::T_COMMA],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['2015-04-19', Token::T_DATE],
                    [',', Token::T_COMMA],
                    ['2012-02-29', Token::T_DATE],
                    [',', Token::T_COMMA],
                    ['2015-02-29', Token::T_STRING],
                    [',', Token::T_COMMA],
                    ['2015-13-19', Token::T_STRING],
                    [')', Token::T_CLOSE_PARENTHESIS],
                ],
            ],
            'datetime support' => [
                'in(a,(2015-04-16T17:40:32Z,2015-04-16T17:40:32,2015-04-16t17:40:32Z,2015-02-30T17:40:32Z))',
                [
                    ['in', Token::T_OPERATOR],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['a', Token::T_STRING],
                    [',', Token::T_COMMA],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['2015-04-16T17:40:32Z', Token::T_DATE],
                    [',', Token::T_COMMA],
                    ['2015-04-16T17:40:32', Token::T_STRING],
                    [',', Token::T_COMMA],
                    ['2015-04-16t17:40:32Z', Token::T_STRING],
                    [',', Token::T_COMMA],
                    ['2015-02-30T17:40:32Z', Token::T_STRING],
                    [')', Token::T_CLOSE_PARENTHESIS],
                ],
            ],

            'simple eq' => [
                'eq(name,value)',
                [
                    ['eq', Token::T_OPERATOR],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['name', Token::T_STRING],
                    [',', Token::T_COMMA],
                    ['value', Token::T_STRING],
                    [')', Token::T_CLOSE_PARENTHESIS],
                ],
            ],
            'array oprators' => [
                'in(a,(1,b))&out(c,(2,d))',
                [
                    ['in', Token::T_OPERATOR],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['a', Token::T_STRING],
                    [',', Token::T_COMMA],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['1', Token::T_INTEGER],
                    [',', Token::T_COMMA],
                    ['b', Token::T_STRING],
                    [')', Token::T_CLOSE_PARENTHESIS],
                    [')', Token::T_CLOSE_PARENTHESIS],

                    ['&', Token::T_AMPERSAND],

                    ['out', Token::T_OPERATOR],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['c', Token::T_STRING],
                    [',', Token::T_COMMA],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['2', Token::T_INTEGER],
                    [',', Token::T_COMMA],
                    ['d', Token::T_STRING],
                    [')', Token::T_CLOSE_PARENTHESIS],
                    [')', Token::T_CLOSE_PARENTHESIS],
                ],
            ],
            'multiple query operators' => [
                'eq(a,b)&lt(c,d)',
                [
                    ['eq', Token::T_OPERATOR],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['a', Token::T_STRING],
                    [',', Token::T_COMMA],
                    ['b', Token::T_STRING],
                    [')', Token::T_CLOSE_PARENTHESIS],

                    ['&', Token::T_AMPERSAND],

                    ['lt', Token::T_OPERATOR],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['c', Token::T_STRING],
                    [',', Token::T_COMMA],
                    ['d', Token::T_STRING],
                    [')', Token::T_CLOSE_PARENTHESIS],
                ],
            ],
            'logic operators' => [
                'and(eq(a,b),lt(c,d),or(in(a,(1,f)),gt(g,2)))&not(ne(h,3))',
                [
                    ['and', Token::T_OPERATOR],
                    ['(', Token::T_OPEN_PARENTHESIS],

                    ['eq', Token::T_OPERATOR],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['a', Token::T_STRING],
                    [',', Token::T_COMMA],
                    ['b', Token::T_STRING],
                    [')', Token::T_CLOSE_PARENTHESIS],

                    [',', Token::T_COMMA],

                    ['lt', Token::T_OPERATOR],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['c', Token::T_STRING],
                    [',', Token::T_COMMA],
                    ['d', Token::T_STRING],
                    [')', Token::T_CLOSE_PARENTHESIS],

                    [',', Token::T_COMMA],

                    ['or', Token::T_OPERATOR],
                    ['(', Token::T_OPEN_PARENTHESIS],

                    ['in', Token::T_OPERATOR],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['a', Token::T_STRING],
                    [',', Token::T_COMMA],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['1', Token::T_INTEGER],
                    [',', Token::T_COMMA],
                    ['f', Token::T_STRING],
                    [')', Token::T_CLOSE_PARENTHESIS],
                    [')', Token::T_CLOSE_PARENTHESIS],

                    [',', Token::T_COMMA],

                    ['gt', Token::T_OPERATOR],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['g', Token::T_STRING],
                    [',', Token::T_COMMA],
                    ['2', Token::T_INTEGER],
                    [')', Token::T_CLOSE_PARENTHESIS],

                    [')', Token::T_CLOSE_PARENTHESIS],
                    [')', Token::T_CLOSE_PARENTHESIS],

                    ['&', Token::T_AMPERSAND],

                    ['not', Token::T_OPERATOR],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['ne', Token::T_OPERATOR],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['h', Token::T_STRING],
                    [',', Token::T_COMMA],
                    ['3', Token::T_INTEGER],
                    [')', Token::T_CLOSE_PARENTHESIS],
                    [')', Token::T_CLOSE_PARENTHESIS],
                ],
            ],

            'select, sort and limit operators' => [
                'select(a,b,c)&sort(a,+b,-c)&limit(1)&limit(1,2)',
                [
                    ['select', Token::T_OPERATOR],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['a', Token::T_STRING],
                    [',', Token::T_COMMA],
                    ['b', Token::T_STRING],
                    [',', Token::T_COMMA],
                    ['c', Token::T_STRING],
                    [')', Token::T_CLOSE_PARENTHESIS],

                    ['&', Token::T_AMPERSAND],

                    ['sort', Token::T_OPERATOR],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['a', Token::T_STRING],
                    [',', Token::T_COMMA],
                    ['+b', Token::T_STRING],
                    [',', Token::T_COMMA],
                    ['-c', Token::T_STRING],
                    [')', Token::T_CLOSE_PARENTHESIS],

                    ['&', Token::T_AMPERSAND],

                    ['limit', Token::T_OPERATOR],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['1', Token::T_INTEGER],
                    [')', Token::T_CLOSE_PARENTHESIS],

                    ['&', Token::T_AMPERSAND],

                    ['limit', Token::T_OPERATOR],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['1', Token::T_INTEGER],
                    [',', Token::T_COMMA],
                    ['2', Token::T_INTEGER],
                    [')', Token::T_CLOSE_PARENTHESIS],
                ],
            ],

            'string typecast' => [
                'eq(a,string:3)&in(b,(string:true(),string:false,string:null,string:empty()))&out(c,(string:-1,string:+.5e10))',
                [
                    ['eq', Token::T_OPERATOR],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['a', Token::T_STRING],
                    [',', Token::T_COMMA],
                    ['string', Token::T_TYPE],
                    ['3', Token::T_INTEGER],
                    [')', Token::T_CLOSE_PARENTHESIS],

                    ['&', Token::T_AMPERSAND],

                    ['in', Token::T_OPERATOR],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['b', Token::T_STRING],
                    [',', Token::T_COMMA],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['string', Token::T_TYPE],
                    ['true()', Token::T_TRUE],
                    [',', Token::T_COMMA],
                    ['string', Token::T_TYPE],
                    ['false', Token::T_FALSE],
                    [',', Token::T_COMMA],
                    ['string', Token::T_TYPE],
                    ['null', Token::T_NULL],
                    [',', Token::T_COMMA],
                    ['string', Token::T_TYPE],
                    ['empty()', Token::T_EMPTY],
                    [')', Token::T_CLOSE_PARENTHESIS],
                    [')', Token::T_CLOSE_PARENTHESIS],

                    ['&', Token::T_AMPERSAND],

                    ['out', Token::T_OPERATOR],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['c', Token::T_STRING],
                    [',', Token::T_COMMA],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['string', Token::T_TYPE],
                    ['-1', Token::T_INTEGER],
                    [',', Token::T_COMMA],
                    ['string', Token::T_TYPE],
                    ['+.5e10', Token::T_FLOAT],
                    [')', Token::T_CLOSE_PARENTHESIS],
                    [')', Token::T_CLOSE_PARENTHESIS],
                ],
            ],
            'constants' => [
                'in(a,(null,null(),true,true(),false,false(),empty()))',
                [
                    ['in', Token::T_OPERATOR],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['a', Token::T_STRING],
                    [',', Token::T_COMMA],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['null', Token::T_NULL],
                    [',', Token::T_COMMA],
                    ['null()', Token::T_NULL],
                    [',', Token::T_COMMA],
                    ['true', Token::T_TRUE],
                    [',', Token::T_COMMA],
                    ['true()', Token::T_TRUE],
                    [',', Token::T_COMMA],
                    ['false', Token::T_FALSE],
                    [',', Token::T_COMMA],
                    ['false()', Token::T_FALSE],
                    [',', Token::T_COMMA],
                    ['empty()', Token::T_EMPTY],
                    [')', Token::T_CLOSE_PARENTHESIS],
                    [')', Token::T_CLOSE_PARENTHESIS],
                ],
            ],
            'fiql operators' => [
                'a=eq=1&b=ne=2&c=lt=3&d=gt=4&e=le=5&f=ge=6&g=in=(7,8)&h=out=(9,10)',
                [
                    ['a', Token::T_STRING],
                    ['eq', Token::T_OPERATOR],
                    ['1', Token::T_INTEGER],

                    ['&', Token::T_AMPERSAND],

                    ['b', Token::T_STRING],
                    ['ne', Token::T_OPERATOR],
                    ['2', Token::T_INTEGER],

                    ['&', Token::T_AMPERSAND],

                    ['c', Token::T_STRING],
                    ['lt', Token::T_OPERATOR],
                    ['3', Token::T_INTEGER],

                    ['&', Token::T_AMPERSAND],

                    ['d', Token::T_STRING],
                    ['gt', Token::T_OPERATOR],
                    ['4', Token::T_INTEGER],

                    ['&', Token::T_AMPERSAND],

                    ['e', Token::T_STRING],
                    ['le', Token::T_OPERATOR],
                    ['5', Token::T_INTEGER],

                    ['&', Token::T_AMPERSAND],

                    ['f', Token::T_STRING],
                    ['ge', Token::T_OPERATOR],
                    ['6', Token::T_INTEGER],

                    ['&', Token::T_AMPERSAND],

                    ['g', Token::T_STRING],
                    ['in', Token::T_OPERATOR],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['7', Token::T_INTEGER],
                    [',', Token::T_COMMA],
                    ['8', Token::T_INTEGER],
                    [')', Token::T_CLOSE_PARENTHESIS],

                    ['&', Token::T_AMPERSAND],

                    ['h', Token::T_STRING],
                    ['out', Token::T_OPERATOR],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['9', Token::T_INTEGER],
                    [',', Token::T_COMMA],
                    ['10', Token::T_INTEGER],
                    [')', Token::T_CLOSE_PARENTHESIS],
                ],
            ],
            'fiql operators (json compatible)' => [
                'a=1&b==2&c<>3&d!=4&e<5&f>6&g<=7&h>=8',
                [
                    ['a', Token::T_STRING],
                    ['=', Token::T_OPERATOR],
                    ['1', Token::T_INTEGER],

                    ['&', Token::T_AMPERSAND],

                    ['b', Token::T_STRING],
                    ['==', Token::T_OPERATOR],
                    ['2', Token::T_INTEGER],

                    ['&', Token::T_AMPERSAND],

                    ['c', Token::T_STRING],
                    ['<>', Token::T_OPERATOR],
                    ['3', Token::T_INTEGER],

                    ['&', Token::T_AMPERSAND],

                    ['d', Token::T_STRING],
                    ['!=', Token::T_OPERATOR],
                    ['4', Token::T_INTEGER],

                    ['&', Token::T_AMPERSAND],

                    ['e', Token::T_STRING],
                    ['<', Token::T_OPERATOR],
                    ['5', Token::T_INTEGER],

                    ['&', Token::T_AMPERSAND],

                    ['f', Token::T_STRING],
                    ['>', Token::T_OPERATOR],
                    ['6', Token::T_INTEGER],

                    ['&', Token::T_AMPERSAND],

                    ['g', Token::T_STRING],
                    ['<=', Token::T_OPERATOR],
                    ['7', Token::T_INTEGER],

                    ['&', Token::T_AMPERSAND],

                    ['h', Token::T_STRING],
                    ['>=', Token::T_OPERATOR],
                    ['8', Token::T_INTEGER],
                ],
            ],
            'simple groups' => [
                '(eq(a,b)&lt(c,d))&(ne(e,f)|gt(g,h))',
                [
                    ['(', Token::T_OPEN_PARENTHESIS],

                    ['eq', Token::T_OPERATOR],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['a', Token::T_STRING],
                    [',', Token::T_COMMA],
                    ['b', Token::T_STRING],
                    [')', Token::T_CLOSE_PARENTHESIS],

                    ['&', Token::T_AMPERSAND],

                    ['lt', Token::T_OPERATOR],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['c', Token::T_STRING],
                    [',', Token::T_COMMA],
                    ['d', Token::T_STRING],
                    [')', Token::T_CLOSE_PARENTHESIS],

                    [')', Token::T_CLOSE_PARENTHESIS],

                    ['&', Token::T_AMPERSAND],

                    ['(', Token::T_OPEN_PARENTHESIS],

                    ['ne', Token::T_OPERATOR],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['e', Token::T_STRING],
                    [',', Token::T_COMMA],
                    ['f', Token::T_STRING],
                    [')', Token::T_CLOSE_PARENTHESIS],

                    ['|', Token::T_VERTICAL_BAR],

                    ['gt', Token::T_OPERATOR],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['g', Token::T_STRING],
                    [',', Token::T_COMMA],
                    ['h', Token::T_STRING],
                    [')', Token::T_CLOSE_PARENTHESIS],

                    [')', Token::T_CLOSE_PARENTHESIS],
                ],
            ],
            'deep groups & mix groups with operators' => [
                '(eq(a,b)|lt(c,d)|and(gt(e,f),(ne(g,h)|gt(i,j)|in(k,(l,m,n))|(o<>p&q=le=r))))',
                [
                    ['(', Token::T_OPEN_PARENTHESIS],

                    ['eq', Token::T_OPERATOR],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['a', Token::T_STRING],
                    [',', Token::T_COMMA],
                    ['b', Token::T_STRING],
                    [')', Token::T_CLOSE_PARENTHESIS],

                    ['|', Token::T_VERTICAL_BAR],

                    ['lt', Token::T_OPERATOR],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['c', Token::T_STRING],
                    [',', Token::T_COMMA],
                    ['d', Token::T_STRING],
                    [')', Token::T_CLOSE_PARENTHESIS],

                    ['|', Token::T_VERTICAL_BAR],

                    ['and', Token::T_OPERATOR],
                    ['(', Token::T_OPEN_PARENTHESIS],

                    ['gt', Token::T_OPERATOR],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['e', Token::T_STRING],
                    [',', Token::T_COMMA],
                    ['f', Token::T_STRING],
                    [')', Token::T_CLOSE_PARENTHESIS],

                    [',', Token::T_COMMA],

                    ['(', Token::T_OPEN_PARENTHESIS],

                    ['ne', Token::T_OPERATOR],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['g', Token::T_STRING],
                    [',', Token::T_COMMA],
                    ['h', Token::T_STRING],
                    [')', Token::T_CLOSE_PARENTHESIS],

                    ['|', Token::T_VERTICAL_BAR],

                    ['gt', Token::T_OPERATOR],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['i', Token::T_STRING],
                    [',', Token::T_COMMA],
                    ['j', Token::T_STRING],
                    [')', Token::T_CLOSE_PARENTHESIS],

                    ['|', Token::T_VERTICAL_BAR],

                    ['in', Token::T_OPERATOR],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['k', Token::T_STRING],
                    [',', Token::T_COMMA],
                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['l', Token::T_STRING],
                    [',', Token::T_COMMA],
                    ['m', Token::T_STRING],
                    [',', Token::T_COMMA],
                    ['n', Token::T_STRING],
                    [')', Token::T_CLOSE_PARENTHESIS],
                    [')', Token::T_CLOSE_PARENTHESIS],

                    ['|', Token::T_VERTICAL_BAR],

                    ['(', Token::T_OPEN_PARENTHESIS],
                    ['o', Token::T_STRING],
                    ['<>', Token::T_OPERATOR],
                    ['p', Token::T_STRING],
                    ['&', Token::T_AMPERSAND],
                    ['q', Token::T_STRING],
                    ['le', Token::T_OPERATOR],
                    ['r', Token::T_STRING],
                    [')', Token::T_CLOSE_PARENTHESIS],

                    [')', Token::T_CLOSE_PARENTHESIS],

                    [')', Token::T_CLOSE_PARENTHESIS],

                    [')', Token::T_CLOSE_PARENTHESIS],
                ],
            ],
        ];
    }
}
