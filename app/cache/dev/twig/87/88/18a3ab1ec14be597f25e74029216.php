<?php

/* WebProfilerBundle:Profiler:results.html.twig */
class __TwigTemplate_878818a3ab1ec14be597f25e74029216 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = $this->env->loadTemplate("@WebProfiler/Profiler/layout.html.twig");

        $this->blocks = array(
            'panel' => array($this, 'block_panel'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "@WebProfiler/Profiler/layout.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    public function block_panel($context, array $blocks = array())
    {
        // line 4
        echo "    <h2>Search Results</h2>

    ";
        // line 6
        if ($this->getContext($context, "tokens")) {
            // line 7
            echo "        <table>
            <thead>
                <tr>
                    <th scope=\"col\">Token</th>
                    <th scope=\"col\">IP</th>
                    <th scope=\"col\">Method</th>
                    <th scope=\"col\">URL</th>
                    <th scope=\"col\">Time</th>
                </tr>
            </thead>
            <tbody>
                ";
            // line 18
            $context['_parent'] = (array) $context;
            $context['_seq'] = twig_ensure_traversable($this->getContext($context, "tokens"));
            foreach ($context['_seq'] as $context["_key"] => $context["elements"]) {
                // line 19
                echo "                    <tr>
                        <td><a href=\"";
                // line 20
                echo twig_escape_filter($this->env, $this->env->getExtension('routing')->getPath("_profiler", array("token" => $this->getAttribute($this->getContext($context, "elements"), "token"))), "html", null, true);
                echo "\">";
                echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, "elements"), "token"), "html", null, true);
                echo "</a></td>
                        <td>";
                // line 21
                echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, "elements"), "ip"), "html", null, true);
                echo "</td>
                        <td>";
                // line 22
                echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, "elements"), "method"), "html", null, true);
                echo "</td>
                        <td>";
                // line 23
                echo twig_escape_filter($this->env, $this->getAttribute($this->getContext($context, "elements"), "url"), "html", null, true);
                echo "</td>
                        <td>";
                // line 24
                echo twig_escape_filter($this->env, twig_date_format_filter($this->env, $this->getAttribute($this->getContext($context, "elements"), "time"), "r"), "html", null, true);
                echo "</td>
                    </tr>
                ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['elements'], $context['_parent'], $context['loop']);
            $context = array_merge($_parent, array_intersect_key($context, $_parent));
            // line 27
            echo "            </tbody>
        </table>
    ";
        } else {
            // line 30
            echo "        <p>
            <em>The query returned no result.</em>
        </p>
    ";
        }
        // line 34
        echo "
";
    }

    public function getTemplateName()
    {
        return "WebProfilerBundle:Profiler:results.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  20 => 1,  178 => 66,  175 => 65,  118 => 49,  400 => 180,  396 => 179,  388 => 177,  386 => 176,  378 => 170,  369 => 165,  348 => 153,  334 => 145,  293 => 118,  276 => 113,  273 => 112,  271 => 111,  262 => 104,  259 => 103,  248 => 96,  240 => 92,  221 => 85,  219 => 84,  195 => 71,  191 => 67,  172 => 64,  150 => 55,  134 => 54,  113 => 48,  81 => 23,  385 => 160,  382 => 159,  376 => 169,  367 => 156,  363 => 155,  359 => 153,  357 => 152,  354 => 151,  351 => 150,  349 => 149,  339 => 146,  336 => 145,  330 => 141,  317 => 135,  311 => 131,  308 => 130,  292 => 121,  289 => 120,  286 => 119,  284 => 118,  279 => 115,  277 => 114,  272 => 111,  270 => 110,  261 => 105,  251 => 97,  249 => 100,  242 => 96,  237 => 93,  228 => 88,  225 => 87,  223 => 86,  218 => 83,  206 => 77,  204 => 76,  180 => 63,  159 => 53,  148 => 46,  100 => 39,  344 => 119,  332 => 116,  327 => 141,  324 => 139,  321 => 112,  318 => 111,  315 => 110,  306 => 107,  303 => 128,  300 => 105,  297 => 104,  291 => 102,  288 => 116,  274 => 97,  265 => 107,  263 => 95,  255 => 103,  243 => 93,  231 => 89,  212 => 79,  202 => 75,  190 => 68,  185 => 67,  174 => 59,  161 => 63,  104 => 32,  34 => 5,  791 => 473,  788 => 472,  777 => 470,  773 => 469,  769 => 467,  756 => 466,  730 => 461,  727 => 460,  708 => 458,  691 => 457,  687 => 455,  683 => 454,  679 => 453,  675 => 452,  671 => 451,  667 => 450,  663 => 449,  660 => 448,  658 => 447,  641 => 446,  630 => 445,  615 => 440,  610 => 438,  606 => 437,  603 => 436,  601 => 435,  587 => 434,  550 => 399,  532 => 396,  515 => 395,  512 => 394,  510 => 393,  505 => 391,  500 => 389,  244 => 97,  188 => 68,  170 => 84,  153 => 56,  97 => 23,  63 => 21,  58 => 14,  59 => 22,  127 => 60,  110 => 22,  102 => 40,  90 => 37,  76 => 28,  53 => 12,  480 => 162,  474 => 161,  469 => 158,  461 => 155,  457 => 153,  453 => 151,  444 => 149,  440 => 148,  437 => 147,  435 => 146,  430 => 144,  427 => 143,  423 => 142,  413 => 134,  409 => 183,  407 => 131,  402 => 130,  398 => 129,  393 => 178,  387 => 122,  384 => 121,  381 => 120,  379 => 119,  374 => 157,  368 => 112,  365 => 111,  362 => 161,  360 => 109,  355 => 157,  341 => 149,  337 => 103,  322 => 138,  314 => 99,  312 => 129,  309 => 108,  305 => 125,  298 => 121,  294 => 90,  285 => 115,  283 => 100,  278 => 98,  268 => 85,  264 => 84,  258 => 94,  252 => 80,  247 => 78,  241 => 77,  235 => 89,  229 => 87,  224 => 81,  220 => 70,  214 => 69,  208 => 68,  169 => 60,  143 => 43,  140 => 58,  132 => 51,  128 => 49,  119 => 40,  107 => 27,  71 => 23,  38 => 6,  177 => 64,  165 => 60,  160 => 61,  135 => 62,  126 => 45,  114 => 42,  84 => 27,  70 => 19,  67 => 22,  61 => 23,  87 => 34,  94 => 34,  89 => 30,  85 => 24,  75 => 24,  68 => 30,  56 => 11,  196 => 92,  183 => 70,  171 => 58,  166 => 56,  163 => 82,  158 => 62,  156 => 58,  151 => 59,  142 => 59,  138 => 57,  136 => 48,  121 => 50,  117 => 39,  105 => 34,  91 => 33,  62 => 27,  49 => 14,  26 => 11,  93 => 38,  88 => 20,  78 => 26,  46 => 12,  28 => 3,  24 => 3,  44 => 9,  31 => 4,  27 => 3,  25 => 35,  21 => 2,  19 => 1,  79 => 18,  72 => 27,  69 => 26,  47 => 8,  40 => 6,  37 => 7,  22 => 2,  246 => 32,  157 => 56,  145 => 52,  139 => 49,  131 => 45,  123 => 42,  120 => 20,  115 => 43,  111 => 47,  108 => 47,  101 => 43,  98 => 30,  96 => 37,  83 => 35,  74 => 27,  66 => 15,  55 => 15,  52 => 12,  50 => 18,  43 => 12,  41 => 8,  35 => 6,  32 => 7,  29 => 3,  209 => 79,  203 => 78,  199 => 73,  193 => 69,  189 => 71,  187 => 67,  182 => 87,  176 => 86,  173 => 85,  168 => 61,  164 => 59,  162 => 59,  154 => 60,  149 => 51,  147 => 54,  144 => 53,  141 => 51,  133 => 55,  130 => 46,  125 => 51,  122 => 41,  116 => 39,  112 => 36,  109 => 35,  106 => 51,  103 => 25,  99 => 31,  95 => 34,  92 => 21,  86 => 28,  82 => 19,  80 => 29,  73 => 20,  64 => 24,  60 => 6,  57 => 20,  54 => 19,  51 => 13,  48 => 9,  45 => 10,  42 => 8,  39 => 10,  36 => 8,  33 => 4,  30 => 3,);
    }
}
