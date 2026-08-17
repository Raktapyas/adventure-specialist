---
paths:
  - '.tmp/tasks/**'
---

# Tasks

## task-cli broken under Node v25; edit JSON directly
The task-management router.sh (ts-node) crashes under Node v25.2.1 with "Cannot read properties of undefined (reading 'fileExists')". To mark subtasks complete, edit .tmp/tasks/{feature}/subtask_{seq}.json directly (status/started_at/completed_at/completion_summary) instead of running router.sh complete.
