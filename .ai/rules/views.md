---
paths:
  - 'resources/views/**'
---

# Views

## Center section content without shrinking the inner container
To vertically center a section's content, making the section `flex flex-col justify-center` is fine, but the inner `mx-auto max-w-[1240px]` wrapper MUST become `w-full max-w-[1240px]` and the section needs `items-center`. Auto cross-axis margins in a flex container prevent `align-items: stretch`, so `mx-auto` collapses the container to its fit-content width (e.g. 1240px -> 936px, shrinking cards from 381px to 280px). Use `w-full` + `items-center` to preserve the max-width and horizontal centering. This is how #services on the home page is built.

## Frontend renders media by type via x-media-file
x-media-file (src, type, alt) renders <img> or <video autoplay muted loop playsinline preload="metadata">. Hero slider: the Ken Burns class lives on the wrapper div (not the img) so video slides animate too; slide shape carries 'type' from HeroSlide::toSlide() via the eager-loaded media relation. Gallery lightbox handles both kinds: images keep glide-from-thumb, videos fade in centered at fixed 16:9 and are paused/unloaded on close. Home fluid grid pads empty tiles with the first IMAGE so an autoplaying clip is never duplicated across tiles.
