# Nether Sensei

Static documentation generator. The more verbose you write your code, the more
info it will be able to find, inversely requiring less phpDoc blocks.

# Requirements

* PHP 8+

# Installation

It will be installed via Composer once ready. If this was a video game I'd
tag it as early access.

# Usage

Run through a directory of code and compile an index. Without any options
supplied it will create a file called `nether-sensei-data.phson` in the
current working directory.

> $ nsen compile src

Build a directory of HTML from the code index. Without any options supplied
it will be built in a directory called `docs` in the current working
directory.

> $ nsen render
