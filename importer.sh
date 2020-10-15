# Find a way to merge these three commands
rename 's:/:-:g' */index.mdx
rename "s/-index.mdx/.md/" *.mdx
find . -type d -empty -delete

# Update yaml frontmatter values
sed -i '/^layout:/d' *.md
sed -i -r "s#date: '(.*)'.*#date '+updated_at: %s' -d '\1'#e" *.md

# TODO: Replace author slugs with uuid for Statamic Users'
#       - Need to swap the key from author to updated_by ?
#           - Probably need an author field on Statamic Posts :/
#       - Perhaps we can use awk here instead? Build a dictionary of Author:UUID

^author:\s'?([a-zA-Z ]*),? ?((?1))'?
