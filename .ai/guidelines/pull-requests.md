# Pull Requests

- Fill every PR description from `.github/pull_request_template.md`. GitHub pre-fills the template only in the web UI and in interactive `gh pr create`; it does not apply to `gh pr create --body-file` or to the GitHub MCP `create_pull_request`. Open the file, keep its headings and order, replace each hint comment with content, and leave no section empty.
- The template's structure wins over the `/pr` skill: `/pr` adds no headings of its own, and its guidance applies inside `Summary`, `Evidence`, and `Merge Danger`.
- Put every link in `Контекст`: the original request, the spec, the tickets, and the human approval. Add a `Closes #N` line for each issue the PR completes; leave out a spec that still has open tickets.
- In `Evidence`, give the commands you ran, links to the CI runs, and the SHA they checked. In `Откат`, say how to revert in Git, how to check that the old behaviour is back, and whether reverting the code is enough when the PR changes data.
