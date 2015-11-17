# Sass

Things to do first:

* Install Ruby, Ruby-dev and the generic GNU compilers
* Read about [Sass][1]

## Install dependencies

Ruby, Ruby-dev and c/c++ compilers need to be installed. If you're running a Debian based distro, then you can use somthing like this:

`apt-get install ruby ruby-dev build-essential`

Then, go to `web/bundles/imdcterptube/_css/` and run the following:

`bundle install`

## Conventions for TerpTube development

* Style names are hyphenated
* Style names have the prefix "tt", then the section name (styles defined in _base.scss are exempt from section naming)

```css
 /* example */
 .tt-forum-thumbnail {
   /* ... */
 }
```

[1]: http://sass-lang.com/guide
