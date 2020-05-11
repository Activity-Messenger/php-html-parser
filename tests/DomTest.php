<?php

declare(strict_types=1);

use PHPHtmlParser\Dom;
use PHPUnit\Framework\TestCase;

class DomTest extends TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    /**
     * <![CDATA[ should not be modified when cleanupInput is set to false.
     */
    public function testParsingCData()
    {
        $html = "<script type=\"text/javascript\">/* <![CDATA[ */var et_core_api_spam_recaptcha = '';/* ]]> */</script>";
        $dom = new Dom();
        $dom->setOptions(['cleanupInput' => false]);
        $dom->load($html);
        $this->assertSame($html, $dom->root->outerHtml());
    }

    public function testLoad()
    {
        $dom = new Dom();
        $dom->load('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>');
        $div = $dom->find('div', 0);
        $this->assertEquals('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>', $div->outerHtml);
    }

    /**
     * @expectedException \PHPHtmlParser\Exceptions\NotLoadedException
     */
    public function testNotLoaded()
    {
        $dom = new Dom();
        $div = $dom->find('div', 0);
    }

    public function testIncorrectAccess()
    {
        $dom = new Dom();
        $dom->load('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>');
        $div = $dom->find('div', 0);
        $this->assertEquals(null, $div->foo);
    }

    public function testLoadSelfclosingAttr()
    {
        $dom = new Dom();
        $dom->load("<div class='all'><br  foo  bar  />baz</div>");
        $br = $dom->find('br', 0);
        $this->assertEquals('<br foo bar />', $br->outerHtml);
    }

    public function testLoadSelfclosingAttrToString()
    {
        $dom = new Dom();
        $dom->load("<div class='all'><br  foo  bar  />baz</div>");
        $br = $dom->find('br', 0);
        $this->assertEquals('<br foo bar />', (string) $br);
    }

    public function testLoadEscapeQuotes()
    {
        $dom = new Dom();
        $dom->load('<div class="all"><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></p></div>');
        $div = $dom->find('div', 0);
        $this->assertEquals('<div class="all"><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></p></div>', $div->outerHtml);
    }

    public function testLoadNoOpeningTag()
    {
        $dom = new Dom();
        $dom->load('<div class="all"><font color="red"><strong>PR Manager</strong></font></b><div class="content">content</div></div>');
        $this->assertEquals('content', $dom->find('.content', 0)->text);
    }

    public function testLoadNoClosingTag()
    {
        $dom = new Dom();
        $dom->load('<div class="all"><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></div><br />');
        $root = $dom->find('div', 0)->getParent();
        $this->assertEquals('<div class="all"><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></p></div><br />', $root->outerHtml);
    }

    public function testLoadAttributeOnSelfClosing()
    {
        $dom = new Dom();
        $dom->load('<div class="all"><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></div><br class="both" />');
        $br = $dom->find('br', 0);
        $this->assertEquals('both', $br->getAttribute('class'));
    }

    public function testLoadClosingTagOnSelfClosing()
    {
        $dom = new Dom();
        $dom->load('<div class="all"><br><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></br></div>');
        $this->assertEquals('<br /><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></p>', $dom->find('div', 0)->innerHtml);
    }

    public function testLoadClosingTagOnSelfClosingNoSlash()
    {
        $dom = new Dom();
        $dom->addNoSlashTag('br');

        $dom->load('<div class="all"><br><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></br></div>');
        $this->assertEquals('<br><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></p>', $dom->find('div', 0)->innerHtml);
    }

    public function testLoadClosingTagAddSelfClosingTag()
    {
        $dom = new Dom();
        $dom->addSelfClosingTag('mytag');
        $dom->load('<div class="all"><mytag><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></mytag></div>');
        $this->assertEquals('<mytag /><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></p>', $dom->find('div', 0)->innerHtml);
    }

    public function testLoadClosingTagAddSelfClosingTagArray()
    {
        $dom = new Dom();
        $dom->addSelfClosingTag([
            'mytag',
            'othertag',
        ]);
        $dom->load('<div class="all"><mytag><p>Hey bro, <a href="google.com" data-quote="\"">click here</a><othertag></div>');
        $this->assertEquals('<mytag /><p>Hey bro, <a href="google.com" data-quote="\"">click here</a><othertag /></p>', $dom->find('div', 0)->innerHtml);
    }

    public function testLoadClosingTagRemoveSelfClosingTag()
    {
        $dom = new Dom();
        $dom->removeSelfClosingTag('br');
        $dom->load('<div class="all"><br><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></br></div>');
        $this->assertEquals('<br><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></p></br>', $dom->find('div', 0)->innerHtml);
    }

    public function testLoadClosingTagClearSelfClosingTag()
    {
        $dom = new Dom();
        $dom->clearSelfClosingTags();
        $dom->load('<div class="all"><br><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></br></div>');
        $this->assertEquals('<br><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></p></br>', $dom->find('div', 0)->innerHtml);
    }

    public function testLoadNoValueAttribute()
    {
        $dom = new Dom();
        $dom->load('<div class="content"><div class="grid-container" ui-view>Main content here</div></div>');
        $this->assertEquals('<div class="content"><div class="grid-container" ui-view>Main content here</div></div>', $dom->innerHtml);
    }

    public function testLoadBackslashAttributeValue()
    {
        $dom = new Dom();
        $dom->load('<div class="content"><div id="\" class="grid-container" ui-view>Main content here</div></div>');
        $this->assertEquals('<div class="content"><div id="\" class="grid-container" ui-view>Main content here</div></div>', $dom->innerHtml);
    }

    public function testLoadNoValueAttributeBefore()
    {
        $dom = new Dom();
        $dom->load('<div class="content"><div ui-view class="grid-container">Main content here</div></div>');
        $this->assertEquals('<div class="content"><div ui-view class="grid-container">Main content here</div></div>', $dom->innerHtml);
    }

    public function testLoadUpperCase()
    {
        $dom = new Dom();
        $dom->load('<DIV CLASS="ALL"><BR><P>hEY BRO, <A HREF="GOOGLE.COM" DATA-QUOTE="\"">CLICK HERE</A></BR></DIV>');
        $this->assertEquals('<br /><p>hEY BRO, <a href="GOOGLE.COM" data-quote="\"">CLICK HERE</a></p>', $dom->find('div', 0)->innerHtml);
    }

    public function testLoadWithFile()
    {
        $dom = new Dom();
        $dom->loadFromFile('tests/data/files/small.html');
        $this->assertEquals('VonBurgermeister', $dom->find('.post-user font', 0)->text);
    }

    public function testLoadFromFile()
    {
        $dom = new Dom();
        $dom->loadFromFile('tests/data/files/small.html');
        $this->assertEquals('VonBurgermeister', $dom->find('.post-user font', 0)->text);
    }

    public function testLoadFromFileFind()
    {
        $dom = new Dom();
        $dom->loadFromFile('tests/data/files/small.html');
        $this->assertEquals('VonBurgermeister', $dom->find('.post-row div .post-user font', 0)->text);
    }

    public function testLoadFromFileNotFound()
    {
        $dom = new Dom();
        $this->expectException(\PHPHtmlParser\Exceptions\LogicalException::class);
        $dom->loadFromFile('tests/data/files/unkowne.html');
    }

    public function testLoadUtf8()
    {
        $dom = new Dom();
        $dom->load('<p>Dzień</p>');
        $this->assertEquals('Dzień', $dom->find('p', 0)->text);
    }

    public function testLoadFileWhitespace()
    {
        $dom = new Dom();
        $dom->setOptions(['cleanupInput' => false]);
        $dom->loadFromFile('tests/data/files/whitespace.html');
        $this->assertEquals(1, \count($dom->find('.class')));
        $this->assertEquals('<span><span class="class"></span></span>', (string) $dom);
    }

    public function testLoadFileBig()
    {
        $dom = new Dom();
        $dom->loadFromFile('tests/data/files/big.html');
        $this->assertEquals(20, \count($dom->find('.content-border')));
    }

    public function testLoadFileBigTwice()
    {
        $dom = new Dom();
        $dom->loadFromFile('tests/data/files/big.html');
        $post = $dom->find('.post-row', 0);
        $this->assertEquals(' <p>Журчанье воды<br /> Черно-белые тени<br /> Вновь на фонтане</p> ', $post->find('.post-message', 0)->innerHtml);
    }

    public function testLoadFileBigTwicePreserveOption()
    {
        $dom = new Dom();
        $dom->loadFromFile('tests/data/files/big.html', ['preserveLineBreaks' => true]);
        $post = $dom->find('.post-row', 0);
        $this->assertEquals(
            "<p>Журчанье воды<br />\nЧерно-белые тени<br />\nВновь на фонтане</p>",
            \trim($post->find('.post-message', 0)->innerHtml)
        );
    }

    public function testLoadFromUrl()
    {
        $streamMock = Mockery::mock(\Psr\Http\Message\StreamInterface::class);
        $streamMock->shouldReceive('getContents')
            ->once()
            ->andReturn(\file_get_contents('tests/data/files/small.html'));
        $responseMock = Mockery::mock(\Psr\Http\Message\ResponseInterface::class);
        $responseMock->shouldReceive('getBody')
            ->once()
            ->andReturn($streamMock);
        $clientMock = Mockery::mock(\Psr\Http\Client\ClientInterface::class);
        $clientMock->shouldReceive('sendRequest')
            ->once()
            ->andReturn($responseMock);

        $dom = new Dom();
        $dom->loadFromUrl('http://google.com', [], $clientMock);
        $this->assertEquals('VonBurgermeister', $dom->find('.post-row div .post-user font', 0)->text);
    }

    public function testToStringMagic()
    {
        $dom = new Dom();
        $dom->load('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>');
        $this->assertEquals('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>', (string) $dom);
    }

    public function testGetMagic()
    {
        $dom = new Dom();
        $dom->load('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>');
        $this->assertEquals('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>', $dom->innerHtml);
    }

    public function testFirstChild()
    {
        $dom = new Dom();
        $dom->load('<div class="all"><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></div><br />');
        $this->assertEquals('<div class="all"><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></p></div>', $dom->firstChild()->outerHtml);
    }

    public function testLastChild()
    {
        $dom = new Dom();
        $dom->load('<div class="all"><p>Hey bro, <a href="google.com" data-quote="\"">click here</a></div><br />');
        $this->assertEquals('<br />', $dom->lastChild()->outerHtml);
    }

    public function testGetElementById()
    {
        $dom = new Dom();
        $dom->load('<div class="all"><p>Hey bro, <a href="google.com" id="78">click here</a></div><br />');
        $this->assertEquals('<a href="google.com" id="78">click here</a>', $dom->getElementById('78')->outerHtml);
    }

    public function testGetElementsByTag()
    {
        $dom = new Dom();
        $dom->load('<div class="all"><p>Hey bro, <a href="google.com" id="78">click here</a></div><br />');
        $this->assertEquals('<p>Hey bro, <a href="google.com" id="78">click here</a></p>', $dom->getElementsByTag('p')[0]->outerHtml);
    }

    public function testGetElementsByClass()
    {
        $dom = new Dom();
        $dom->load('<div class="all"><p>Hey bro, <a href="google.com" id="78">click here</a></div><br />');
        $this->assertEquals('<p>Hey bro, <a href="google.com" id="78">click here</a></p>', $dom->getElementsByClass('all')[0]->innerHtml);
    }

    public function testScriptCleanerScriptTag()
    {
        $dom = new Dom();
        $dom->load('
        <p>.....</p>
        <script>
        Some code ... 
        document.write("<script src=\'some script\'><\/script>") 
        Some code ... 
        </script>
        <p>....</p>');
        $this->assertEquals('....', $dom->getElementsByTag('p')[1]->innerHtml);
    }

    public function testClosingSpan()
    {
        $dom = new Dom();
        $dom->load("<div class='foo'></span>sometext</div>");
        $this->assertEquals('sometext', $dom->getElementsByTag('div')[0]->innerHtml);
    }

    public function testMultipleDoubleQuotes()
    {
        $dom = new Dom();
        $dom->load('<a title="This is a "test" of double quotes" href="http://www.example.com">Hello</a>');
        $this->assertEquals('This is a "test" of double quotes', $dom->getElementsByTag('a')[0]->title);
    }

    public function testMultipleSingleQuotes()
    {
        $dom = new Dom();
        $dom->load("<a title='Ain't this the best' href=\"http://www.example.com\">Hello</a>");
        $this->assertEquals("Ain't this the best", $dom->getElementsByTag('a')[0]->title);
    }

    public function testBeforeClosingTag()
    {
        $dom = new Dom();
        $dom->load('<div class="stream-container "  > <div class="stream-item js-new-items-bar-container"> </div> <div class="stream">');
        $this->assertEquals('<div class="stream-container "> <div class="stream-item js-new-items-bar-container"> </div> <div class="stream"></div></div>', (string) $dom);
    }

    public function testCodeTag()
    {
        $dom = new Dom();
        $dom->load('<strong>hello</strong><code class="language-php">$foo = "bar";</code>');
        $this->assertEquals('<strong>hello</strong><code class="language-php">$foo = "bar";</code>', (string) $dom);
    }

    public function testDeleteNode()
    {
        $dom = new Dom();
        $dom->load('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>');
        $a = $dom->find('a')[0];
        $a->delete();
        unset($a);
        $this->assertEquals('<div class="all"><p>Hey bro, <br /> :)</p></div>', (string) $dom);
    }

    public function testCountChildren()
    {
        $dom = new Dom();
        $dom->load('<strong>hello</strong><code class="language-php">$foo = "bar";</code>');
        $this->assertEquals(2, $dom->countChildren());
    }

    public function testGetChildrenArray()
    {
        $dom = new Dom();
        $dom->load('<strong>hello</strong><code class="language-php">$foo = "bar";</code>');
        $this->assertInternalType('array', $dom->getChildren());
    }

    public function testHasChildren()
    {
        $dom = new Dom();
        $dom->load('<strong>hello</strong><code class="language-php">$foo = "bar";</code>');
        $this->assertTrue($dom->hasChildren());
    }

    public function testFindByIdVar1()
    {
        $dom = new Dom();
        $dom->load('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>');
        /** @var Dom\AbstractNode $result */
        $result = $dom->findById(4);
        $this->assertEquals(4, $result->id());
    }

    public function testFindByIdVar2()
    {
        $dom = new Dom();
        $dom->load('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>');
        /** @var Dom\AbstractNode $result */
        $result = $dom->findById(5);
        $this->assertEquals(5, $result->id());
    }

    public function testFindByIdNotFountEleement()
    {
        $dom = new Dom();
        $dom->load('<div class="all"><p>Hey bro, <a href="google.com">click here</a><br /> :)</p></div>');
        /** @var Dom\AbstractNode $result */
        $result = $dom->findById(8);
        $this->assertFalse($result);
    }

    public function testWhitespaceInText()
    {
        $dom = new Dom();
        $dom->setOptions([
            'removeDoubleSpace' => false,
        ]);
        $dom->load('<pre>    Hello world</pre>');
        $this->assertEquals('<pre>    Hello world</pre>', (string) $dom);
    }

    public function testGetComplexAttribute()
    {
        $dom = new Dom();
        $dom->load('<a href="?search=Fort+William&session_type=face&distance=100&uqs=119846&page=4" class="pagination-next">Next <span class="chevron">&gt;</span></a>');
        $href = $dom->find('a', 0)->href;
        $this->assertEquals('?search=Fort+William&session_type=face&distance=100&uqs=119846&page=4', $href);
    }

    public function testGetComplexAttributeHtmlSpecialCharsDecode()
    {
        $dom = new Dom();
        $dom->setOptions(['htmlSpecialCharsDecode' => true]);
        $dom->load('<a href="?search=Fort+William&amp;session_type=face&amp;distance=100&amp;uqs=119846&amp;page=4" class="pagination-next">Next <span class="chevron">&gt;</span></a>');
        $a = $dom->find('a', 0);
        $this->assertEquals('Next <span class="chevron">></span>', $a->innerHtml);
        $href = $a->href;
        $this->assertEquals('?search=Fort+William&session_type=face&distance=100&uqs=119846&page=4', $href);
    }

    public function testGetChildrenNoChildren()
    {
        $dom = new Dom();
        $dom->loadStr('<div>Test <img src="test.jpg"></div>');

        $imgNode = $dom->root->find('img');
        $children = $imgNode->getChildren();
        $this->assertTrue(\count($children) === 0);
    }

    public function testInfiniteLoopNotHappening()
    {
        $dom = new Dom();
        $dom->loadStr('<html>
                <head>
                <meta http-equiv="refresh" content="5; URL=http://www.example.com">
                <meta http-equiv="cache-control" content="no-cache">
                <meta http-equiv="pragma" content="no-cache">
                <meta http-equiv="expires" content="0">
                </head>
                <');

        $metaNodes = $dom->root->find('meta');
        $this->assertEquals(4, \count($metaNodes));
    }

    public function testFindOrder()
    {
        $str = '<p><img src="http://example.com/first.jpg"></p><img src="http://example.com/second.jpg">';
        $dom = new Dom();
        $dom->load($str);
        $images = $dom->find('img');

        $this->assertEquals('<img src="http://example.com/second.jpg" />', (string) $images[0]);
    }

    public function testFindDepthFirstSearch()
    {
        $str = '<p><img src="http://example.com/first.jpg"></p><img src="http://example.com/second.jpg">';
        $dom = new Dom();
        $dom->setOptions([
            'depthFirstSearch' => true,
        ]);
        $dom->load($str);
        $images = $dom->find('img');

        $this->assertEquals('<img src="http://example.com/first.jpg" />', (string) $images[0]);
    }

    public function testCaseInSensitivity()
    {
        $str = "<FooBar Attribute='asdf'>blah</FooBar>";
        $dom = new Dom();
        $dom->loadStr($str);

        $FooBar = $dom->find('FooBar');
        $this->assertEquals('asdf', $FooBar->getAttribute('attribute'));
    }

    public function testCaseSensitivity()
    {
        $str = "<FooBar Attribute='asdf'>blah</FooBar>";
        $dom = new Dom();
        $dom->loadStr($str);

        $FooBar = $dom->find('FooBar');
        $this->assertEquals('asdf', $FooBar->Attribute);
    }

    public function testEmptyAttribute()
    {
        $str = '<ul class="summary"><li class></li>blah<li class="foo">what</li></ul>';
        $dom = new Dom();
        $dom->load($str);

        $items = $dom->find('.summary .foo');
        $this->assertEquals(1, \count($items));
    }

    public function testMultipleSquareSelector()
    {
        $dom = new Dom();
        $dom->load('<input name="foo" type="text" baz="fig">');

        $items = $dom->find('input[type=text][name=foo][baz=fig]');
        $this->assertEquals(1, \count($items));
    }

    public function testNotSquareSelector()
    {
        $dom = new Dom();
        $dom->load('<input name="foo" type="text" baz="fig">');

        $items = $dom->find('input[type!=foo]');
        $this->assertEquals(1, \count($items));
    }

    public function testStartSquareSelector()
    {
        $dom = new Dom();
        $dom->load('<input name="foo" type="text" baz="fig">');

        $items = $dom->find('input[name^=f]');
        $this->assertEquals(1, \count($items));
    }

    public function testEndSquareSelector()
    {
        $dom = new Dom();
        $dom->load('<input name="foo" type="text" baz="fig">');

        $items = $dom->find('input[baz$=g]');
        $this->assertEquals(1, \count($items));
    }

    public function testStarSquareSelector()
    {
        $dom = new Dom();
        $dom->load('<input name="foo" type="text" baz="fig">');

        $items = $dom->find('input[baz*=*]');
        $this->assertEquals(1, \count($items));
    }

    public function testStarFullRegexSquareSelector()
    {
        $dom = new Dom();
        $dom->load('<input name="foo" type="text" baz="fig">');

        $items = $dom->find('input[baz*=/\w+/]');
        $this->assertEquals(1, \count($items));
    }

    public function testFailedSquareSelector()
    {
        $dom = new Dom();
        $dom->load('<input name="foo" type="text" baz="fig">');

        $items = $dom->find('input[baz%=g]');
        $this->assertEquals(1, \count($items));
    }

    public function testLoadGetAttributeWithBackslash()
    {
        $dom = new Dom();
        $dom->load('<div><a href="/test/"><img alt="\" src="/img/test.png" /><br /></a><a href="/demo/"><img alt="demo" src="/img/demo.png" /></a></div>');
        $imgs = $dom->find('img', 0);
        $this->assertEquals('/img/test.png', $imgs->getAttribute('src'));
    }

    public function test25ChildrenFound()
    {
        $dom = new Dom();
        $dom->setOptions(['whitespaceTextNode' => false]);
        $dom->loadFromFile('tests/data/files/51children.html');
        $children = $dom->find('#red-line-g *');
        $this->assertEquals(25, \count($children));
    }

    public function testHtml5PageLoad()
    {
        $dom = new Dom();
        $dom->loadFromFile('tests/data/files/html5.html');

        /** @var Dom\AbstractNode $meta */
        $div = $dom->find('div.d-inline-block', 0);
        $this->assertEquals('max-width: 29px', $div->getAttribute('style'));
    }

    public function testFindAttributeInBothParentAndChild()
    {
        $dom = new Dom();
        $dom->load('<parent attribute="something">
    <child attribute="anything"></child>
</parent>');

        /** @var Dom\AbstractNode $meta */
        $nodes = $dom->find('[attribute]');
        $this->assertCount(2, $nodes);
    }
}
