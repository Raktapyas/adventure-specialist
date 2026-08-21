---
paths:
  - 'resources/views/**'
---

# Views

## Center section content without shrinking the inner container
To vertically center a section's content, making the section `flex flex-col justify-center` is fine, but the inner `mx-auto max-w-[1240px]` wrapper MUST become `w-full max-w-[1240px]` and the section needs `items-center`. Auto cross-axis margins in a flex container prevent `align-items: stretch`, so `mx-auto` collapses the container to its fit-content width (e.g. 1240px -> 936px, shrinking cards from 381px to 280px). Use `w-full` + `items-center` to preserve the max-width and horizontal centering. This is how #services on the home page is built.
