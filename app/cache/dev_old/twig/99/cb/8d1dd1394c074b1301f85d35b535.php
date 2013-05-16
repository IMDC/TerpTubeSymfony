<?php

/* WebProfilerBundle:Profiler:layout.html.twig */
class __TwigTemplate_99cb8d1dd1394c074b1301f85d35b535 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = $this->env->loadTemplate("@WebProfiler/Profiler/base.html.twig");

        $this->blocks = array(
            'body' => array($this, 'block_body'),
            'panel' => array($this, 'block_panel'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "@WebProfiler/Profiler/base.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    public function block_body($context, array $blocks = array())
    {
        // line 4
        echo "
    ";
        // line 5
        echo $this->env->getExtension('http_kernel')->renderFragment($this->env->getExtension('routing')->getPath("_wdt", array("token" => $this->getContext($context, "token"), "position" => "normal")));
        echo "

    <div id=\"content\">
        ";
        // line 8
        $this->env->loadTemplate("@WebProfiler/Profiler/header.html.twig")->display(array());
        // line 9
        echo "
        <div id=\"main\">

            <div class=\"clear-fix\">
                <div id=\"collector-wrapper\">
                    ";
        // line 14
        if ($this->getContext($context, "profile")) {
            // line 15
            echo "                        <div id=\"resume\">
                            <a id=\"resume-view-all\" href=\"";
            // line 16
            echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("_profiler_search", array("limit" => 10)), "html", null, true);
            echo "\">View last 10</a>
                            <strong>Profile for:</strong>
                            ";
            // line 18
            echo twig_escape_filter($this->env, twig_upper_filter($this->env, $this->getAttribute($this->getContext($context, "profile"), "method")), "html", null, true);
            echo "
                            ";
            // line 19
            if (twig_in_filter(twig_upper_filter($this->env, $this->getAttribute($this->getContext($context, "profile"), "method")), array(0 => "GET", 1 => "HEAD"))) {
                // line 20
                echo "                                <a href=\"";
                echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, "profile"), "url"), "html", null, true);
                echo "\">";
                echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, "profile"), "url"), "html", null, true);
                echo "</a>
                            ";
            } else {
                // line 22
                echo "                                ";
                echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, "profile"), "url"), "html", null, true);
                echo "
                            ";
            }
            // line 24
            echo "                            <span class=\"date\">
                                <em>by ";
            // line 25
            echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, "profile"), "ip"), "html", null, true);
            echo "</em> at <em>";
            echo twig_escape_filter($this->env, twig_date_format_filter($this->env, $this->getAttribute($this->getContext($context, "profile"), "time"), "r"), "html", null, true);
            echo "</em>
                            </span>
                        </div>
                    ";
        }
        // line 29
        echo "
                    <div id=\"collector-content\">
                        ";
        // line 31
        $this->env->loadTemplate("@WebProfiler/Profiler/base_js.html.twig")->display($context);
        // line 32
        echo "                        ";
        $this->displayBlock('panel', $context, $blocks);
        // line 33
        echo "                    </div>
                </div>
                <div id=\"navigation\">
                    ";
        // line 36
        if (array_key_exists("templates", $context)) {
            // line 37
            echo "                        <ul id=\"menu-profiler\">
                            ";
            // line 38
            $context['_parent'] = (array) $context;
            $context['_seq'] = twig_ensure_traversable($this->getContext($context, "templates"));
            foreach ($context['_seq'] as $context["name"] => $context["template"]) {
                // line 39
                echo "                                ";
                ob_start();
                echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, "template"), "renderBlock", array(0 => "menu", 1 => array("collector" => $this->getAttribute($this->getContext($context, "profile"), "getcollector", array(0 => $this->getContext($context, "name")), "method"))), "method"), "html", null, true);
                $context["menu"] = ('' === $tmp = ob_get_clean()) ? '' : new Twig_Markup($tmp, $this->env->getCharset());
                // line 40
                echo "                                ";
                if (($this->getContext($context, "menu") != "")) {
                    // line 41
                    echo "                                    <li class=\"";
                    echo twig_escape_filter($this->env, $this->getContext($context, "name"), "html", null, true);
                    if (($this->getContext($context, "name") == $this->getContext($context, "panel"))) {
                        echo " selected";
                    }
                    echo "\">
                                        <a href=\"";
                    // line 42
                    echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("_profiler", array("token" => $this->getContext($context, "token"), "panel" => $this->getContext($context, "name"))), "html", null, true);
                    echo "\">";
                    echo $this->getContext($context, "menu");
                    echo "</a>
                                    </li>
                                ";
                }
                // line 45
                echo "                            ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['name'], $context['template'], $context['_parent'], $context['loop']);
            $context = array_merge($_parent, array_intersect_key($context, $_parent));
            // line 46
            echo "                            <li class=\"minimize\">
                                <a href=\"javascript:void(0);\" title=\"Minimize toolbar\" onclick=\"return toggleMenuPanels();\">
                                    <span class=\"label\">
                                        <span class=\"icon\"><img id=\"minimizePanelIcon\" src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAhCAYAAADOHBvaAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA4tJREFUeNrkl19IU1Ecx7fZJN2arhq2JUWJlEZL1MAm1B582GBErMJqtabzRRAqFAYa9OfFFx+KQEG3MkYRZkkEJUUwctVSKCJWJojL1ZiydJvLtNXW98AW1znv7t0CHzrw5Xe3nXM/O/f8zvf8LjcajXJWo/E4q9T+P/CaNMZw29rahBMTE3LyoaKi4m1zc/M865swSS6bzZbT09NzfG5urjYUCikikYiQ+ntWVlZQKBQ+F4lE/Var9Ta+CmcK5hsMhpNer/fiwsLCFiYzycnJGZfJZOctFss9uj+wInhwcDDfbDb3+Hy+I+msoVQqNZtMpnNlZWUhxuChoaENHR0djwOBwN5MEkgsFtsGBgY0uPzOJKtzu7q6rmcKJW12dlaJpbqGy7WpwPyWlpZGj8dzkMX9B+l+dLlcda2trbrEHbQEPDY2Jh0dHTWxgF5AxqsRz9J1cjqdlxDWrwTO7u3tNWK7SFhAL8e221WE7pU6Ytk2t7e311JnTQWLJycntWyhpCmVShXCKboBMJyjCKJlzgVzKJiamtrBAKoH1JoAvU+2MN2g6enp3QgboRnqjHl2u70kHA7zU0APpQMlze/357vd7gKyhang7GAwKEsx1gLoAwp0E8JdJtB4w1LK4sw4OAtWl51inBGwAxT/9iLUQb+ZgsHgJ4I5hYWFAQZjbYBrKPB+hGNM4DweL1pUVBRMzOpweXm5WyAQ/GIAfwj4GbZwiUTyIy8vby7eLw4mQH9paamX4VO7kgTeTTeguLjYg0AOjAgVTD58UygUb1i41l84ogGhka4z+rwi9p3MQHwajeYRjrNFlvBbiDfoOmFtQzU1NU/J5JKBA3w+/71er3/J8hA6kSKpOPX19c9w+YH4VDIwqRbG1Wr1HZVK5eX8o6bVaj9XV1cTk3HFcinpsUjs7AWKNwvWO5gpFI93pqmpyYxLB0neVBUIcaI90OHOzs7TfX19ErZA8niNRuNXnU53M2apTmiBSc0lgEoh9cjIiBYV5i6c1YxKYblc/rOhoeEdIin2nkCfoHk2VSYpV7ZCVdB+h8OxDyocHh4WoEJZskRwvUhVVVUIcldWVtrx1XPoNfQFWkynvCWzzIe2QSXQTmg7Od5QT+WinuagliaFnI8kJvQxJpJIATo34zJ8WyR/YF2sfBFDpKCPHyqLMUeajSVniJq9mYKXjUtwPdY34a7W+/EfAQYAZM5kh2zoNWsAAAAASUVORK5CYII=\" alt=\"\" /></span>
                                        <strong>Minimize</strong>
                                    </span>
                                </a>
                            </li>
                        </ul>
                    ";
        }
        // line 56
        echo "                    ";
        echo $this->env->getExtension('http_kernel')->renderFragment($this->env->getExtension('routing')->getPath("_profiler_search_bar"));
        echo "
                    ";
        // line 57
        $this->env->loadTemplate("@WebProfiler/Profiler/admin.html.twig")->display(array("token" => $this->getContext($context, "token")));
        // line 58
        echo "                </div>
            </div>
        </div>
    </div>

    <script type=\"text/javascript\">//<![CDATA[

        function toggleMenuPanels(state, doSave) {
            var leftIconPath = \"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAhCAYAAADOHBvaAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA4tJREFUeNrkl19IU1Ecx7fZJN2arhq2JUWJlEZL1MAm1B582GBErMJqtabzRRAqFAYa9OfFFx+KQEG3MkYRZkkEJUUwctVSKCJWJojL1ZiydJvLtNXW98AW1znv7t0CHzrw5Xe3nXM/O/f8zvf8LjcajXJWo/E4q9T+P/CaNMZw29rahBMTE3LyoaKi4m1zc/M865swSS6bzZbT09NzfG5urjYUCikikYiQ+ntWVlZQKBQ+F4lE/Var9Ta+CmcK5hsMhpNer/fiwsLCFiYzycnJGZfJZOctFss9uj+wInhwcDDfbDb3+Hy+I+msoVQqNZtMpnNlZWUhxuChoaENHR0djwOBwN5MEkgsFtsGBgY0uPzOJKtzu7q6rmcKJW12dlaJpbqGy7WpwPyWlpZGj8dzkMX9B+l+dLlcda2trbrEHbQEPDY2Jh0dHTWxgF5AxqsRz9J1cjqdlxDWrwTO7u3tNWK7SFhAL8e221WE7pU6Ytk2t7e311JnTQWLJycntWyhpCmVShXCKboBMJyjCKJlzgVzKJiamtrBAKoH1JoAvU+2MN2g6enp3QgboRnqjHl2u70kHA7zU0APpQMlze/357vd7gKyhang7GAwKEsx1gLoAwp0E8JdJtB4w1LK4sw4OAtWl51inBGwAxT/9iLUQb+ZgsHgJ4I5hYWFAQZjbYBrKPB+hGNM4DweL1pUVBRMzOpweXm5WyAQ/GIAfwj4GbZwiUTyIy8vby7eLw4mQH9paamX4VO7kgTeTTeguLjYg0AOjAgVTD58UygUb1i41l84ogGhka4z+rwi9p3MQHwajeYRjrNFlvBbiDfoOmFtQzU1NU/J5JKBA3w+/71er3/J8hA6kSKpOPX19c9w+YH4VDIwqRbG1Wr1HZVK5eX8o6bVaj9XV1cTk3HFcinpsUjs7AWKNwvWO5gpFI93pqmpyYxLB0neVBUIcaI90OHOzs7TfX19ErZA8niNRuNXnU53M2apTmiBSc0lgEoh9cjIiBYV5i6c1YxKYblc/rOhoeEdIin2nkCfoHk2VSYpV7ZCVdB+h8OxDyocHh4WoEJZskRwvUhVVVUIcldWVtrx1XPoNfQFWkynvCWzzIe2QSXQTmg7Od5QT+WinuagliaFnI8kJvQxJpJIATo34zJ8WyR/YF2sfBFDpKCPHyqLMUeajSVniJq9mYKXjUtwPdY34a7W+/EfAQYAZM5kh2zoNWsAAAAASUVORK5CYII=\",
                rightIconPath = \"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAhCAYAAADOHBvaAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA4tJREFUeNrsl11IU2EYx91ytrnpmmy4TYNkqGhEJGiiabuQ2EAiZ1kZW9NtF4pgoBB40weJeCHVjReygVmuIhP6gLoJJNQGadhiJcLa2sb8KN2nrprbel7YYh7WzjnOsIsO/Hnes533/Z33PB/nOZRIJJK2GwflP/ifBg8MDGTOzs4eQeOCggJjb2+vH4aRvwGmyeXyZq/Xe9rv99eGQqHs+D+pVKqfxWJNZ2VlPdRoNPfFYnEgVTBNpVI1Op3OG4FAQETkDul0uo3P518dHh6+B6dB0uC5uTlWf3//zcXFRfV2fMjlcsfUarVGIpG4yYCZDQ0Nz10ulziVAGKz2W+7u7ulNTU1q0TAdKVSOWi1Wlt2InqFQuFTvV5/HoYbW+ICc116T0/PBQLQl0TBEB8nYddtKF6SgXNMJtM1nLUuTUxMSMFeIQqfn5+/vLCwIPgTOL2vr++sx+PJS7LGEEBvowHY60ThkII8iHIVDDMSgbMtFssZnDXkkKOS2AkZuM1mk4HhJAJzV1ZWDuHMZ4DGE8AVeODl5eVin8+XiwVT7HZ7rtvt3kfg5hPB74I5lWxSMBikTU5OlsSYMTAVHoWQRJYg+COA8+PgT8Dokk2CkiuM+fk3mMFg0EiAQ6AWgC3FfoCbOA5GlfRuGQwE3bPFxyKRyAvFPkIQeg6gY3HQerRpvIn5+fkerI9DUN58PB4vsA1oJ5hneFAmk7lZVlZmj704YuAwSrfCwkInzvyhBNBbRHxTWlqK3IJeGJvYdHLBQm9w5rfBNUqyUHRUVVW9A7Ma3eSWlwRK7np4/w6azWYWzjp6UDNRqEAg+DEyMtJJo9Eew+k37I59oI+tra2vIMjw1momkQFpCoViGqAfYOhJVLnQs7dWV1ePy2SyLzvV1EEjsCSVSh/A0BzfkWC3hpxv6Ojo0NbV1a2lCgW/eru6ulBRmQKt4TYCoIMg2ejo6EWdTpcXDodJQ5uamr62t7ffgSHy63tQgEjrkwkqBp0wGo2NWq32MNgMIsCioqJN6DRN5eXl43D6AsUNaJ1Ml7kXFRvQUVDtzMzMMYPBsB/EcjgcVEx7E66oqFivrKx0gFBKvkYuA6FY+b6d9hbVVTboAKgkKtTmcqHgM6G/TuNwOBvRFPmMmg3QJ5Alvlik0tCng1Bu50TznRV9Iuj4iaoeKkDRAPIlA6b67UTBlFvSi+zaR9svAQYA6+V18DvfH/8AAAAASUVORK5CYII=\",
                menu = document.getElementById('navigation'), savedState = Sfjs.getPreference('menu/displayState'),
                displayState, elem, className;

            if (savedState == null) {
                savedState = 'block';
            }

            displayState = state || (savedState == 'block' ? 'none' : 'block');

            if (typeof doSave === 'undefined') {
                doSave = true;
            }

            document.getElementById('searchBar').style.display = displayState;
            document.getElementById('adminBar').style.display = displayState;

            if (displayState == 'block') {
                Sfjs.removeClass(menu, 'collapsed-menu');
                Sfjs.removeClass(menu.parentNode.parentNode, 'collapsed-menu-parents');

                document.getElementById('minimizePanelIcon').src = leftIconPath;
            } else {
                Sfjs.addClass(menu, 'collapsed-menu');
                Sfjs.addClass(menu.parentNode.parentNode, 'collapsed-menu-parents');

                document.getElementById('minimizePanelIcon').src = rightIconPath;
            }

            if (doSave) {
                Sfjs.setPreference('menu/displayState', displayState);
            }

            try {
                canvasAutoUpdateOnThresholdChange(null);
            } catch (err) {

            }

            return false;
        }

        window.setTimeout(function() {
            if (document.getElementById('menu-profiler') == null) {
                return;
            }

            var menuItems = document.getElementById('menu-profiler').getElementsByTagName('LI'),
                elem, value, child, displayState = Sfjs.getPreference('menu/displayState');

            if (displayState == 'none') {
                toggleMenuPanels('none', false);
            }

            for (elem in menuItems) {
                if (typeof(menuItems[elem].children) != 'undefined' &&
                    menuItems[elem].children.length > 0) {
                    child = menuItems[elem].children[0]

                    if (child.getAttribute('title') == '' ||
                        child.getAttribute('title') == null) {
                        value = child.text.replace(/^\\s+/g, '').split('\\n')[0].replace(/\\s+\$/g, '');
                        child.setAttribute('title', value);
                    }
                }
            }
        }, 25);

    //]]></script>
";
    }

    // line 32
    public function block_panel($context, array $blocks = array())
    {
        echo "";
    }

    public function getTemplateName()
    {
        return "WebProfilerBundle:Profiler:layout.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  183 => 70,  160 => 61,  135 => 47,  126 => 45,  114 => 42,  78 => 24,  203 => 78,  189 => 71,  149 => 51,  144 => 53,  133 => 42,  95 => 34,  86 => 24,  48 => 9,  57 => 12,  51 => 10,  34 => 5,  31 => 4,  791 => 473,  788 => 472,  777 => 470,  773 => 469,  769 => 467,  756 => 466,  730 => 461,  727 => 460,  708 => 458,  691 => 457,  687 => 455,  683 => 454,  679 => 453,  675 => 452,  671 => 451,  667 => 450,  663 => 449,  660 => 448,  658 => 447,  641 => 446,  630 => 445,  615 => 440,  610 => 438,  606 => 437,  603 => 436,  601 => 435,  587 => 434,  550 => 399,  532 => 396,  515 => 395,  512 => 394,  510 => 393,  505 => 391,  500 => 389,  196 => 92,  182 => 68,  176 => 64,  173 => 63,  170 => 84,  163 => 82,  68 => 30,  62 => 27,  28 => 3,  344 => 119,  332 => 116,  321 => 112,  318 => 111,  315 => 110,  309 => 108,  306 => 107,  300 => 105,  297 => 104,  291 => 102,  283 => 100,  278 => 98,  274 => 97,  263 => 95,  258 => 94,  224 => 81,  136 => 71,  122 => 44,  117 => 39,  112 => 35,  109 => 41,  104 => 32,  85 => 24,  75 => 23,  58 => 25,  44 => 10,  178 => 66,  175 => 65,  161 => 63,  158 => 80,  154 => 54,  125 => 38,  121 => 50,  118 => 49,  87 => 41,  49 => 14,  46 => 13,  27 => 3,  91 => 33,  63 => 18,  385 => 160,  382 => 159,  374 => 157,  367 => 156,  363 => 155,  359 => 153,  357 => 152,  354 => 151,  351 => 150,  349 => 149,  339 => 146,  336 => 145,  330 => 141,  324 => 113,  322 => 138,  317 => 135,  311 => 131,  308 => 130,  303 => 106,  292 => 121,  289 => 120,  286 => 119,  284 => 118,  279 => 115,  277 => 114,  272 => 111,  270 => 110,  265 => 96,  261 => 105,  255 => 93,  249 => 100,  244 => 136,  242 => 96,  237 => 93,  231 => 83,  228 => 88,  225 => 87,  223 => 86,  218 => 83,  212 => 78,  206 => 77,  204 => 76,  199 => 76,  193 => 73,  190 => 76,  187 => 70,  180 => 63,  174 => 65,  171 => 58,  166 => 56,  159 => 53,  151 => 57,  148 => 46,  143 => 51,  140 => 58,  107 => 27,  103 => 37,  100 => 39,  97 => 23,  88 => 25,  82 => 22,  76 => 34,  61 => 16,  39 => 6,  36 => 5,  79 => 21,  72 => 18,  69 => 17,  54 => 12,  47 => 9,  42 => 7,  40 => 11,  37 => 10,  22 => 1,  246 => 32,  164 => 58,  157 => 56,  145 => 46,  139 => 45,  131 => 42,  120 => 40,  115 => 39,  111 => 38,  108 => 37,  106 => 36,  101 => 33,  98 => 32,  92 => 29,  83 => 25,  80 => 24,  74 => 22,  66 => 20,  60 => 18,  55 => 16,  52 => 15,  50 => 14,  41 => 8,  32 => 4,  29 => 3,  409 => 183,  400 => 180,  396 => 179,  393 => 178,  388 => 177,  386 => 176,  378 => 170,  376 => 158,  369 => 165,  362 => 161,  355 => 157,  348 => 153,  341 => 118,  334 => 145,  327 => 114,  312 => 109,  305 => 129,  298 => 125,  293 => 118,  288 => 101,  285 => 115,  276 => 113,  273 => 112,  271 => 111,  262 => 104,  259 => 103,  251 => 101,  248 => 96,  243 => 92,  240 => 92,  235 => 85,  229 => 87,  221 => 85,  219 => 84,  209 => 82,  202 => 77,  195 => 71,  191 => 67,  188 => 90,  185 => 74,  177 => 69,  172 => 64,  168 => 66,  165 => 64,  162 => 57,  156 => 58,  153 => 77,  150 => 55,  147 => 55,  141 => 51,  134 => 54,  130 => 41,  123 => 41,  119 => 40,  116 => 36,  113 => 48,  105 => 34,  102 => 40,  99 => 30,  96 => 31,  90 => 42,  84 => 28,  81 => 23,  73 => 19,  70 => 20,  67 => 19,  64 => 19,  59 => 14,  53 => 12,  45 => 8,  43 => 9,  38 => 7,  35 => 5,  33 => 4,  30 => 3,);
    }
}
