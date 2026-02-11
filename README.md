# Evoke Game Local Plugin #

TODO Describe the plugin shortly here.

TODO Provide more detailed description here.

## Skill points: submit vs graded

Skill points can be awarded when the student **submits** the activity or when the activity is **graded** (corrected). This is configured **per activity** in the module settings (assign, evokeportfolio, portfoliobuilder, portfoliogroup):

- **Submission skills** — points awarded when the student submits (event `assessable_submitted`).
- **Grade skills** — points awarded when the activity is graded (events `user_graded` or `submission_graded` for assign).

To release skill points **only when the activity is graded** (not on submit):

1. In the activity settings, leave **Submission skills** at "Choose a value" (0) for all skills.
2. Set **Grade skills** with the desired points per skill.

Skill points are then awarded only after the teacher/corrector grades the activity. Badge and xAPI behaviour is unchanged.

## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/local/evokegame

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.
