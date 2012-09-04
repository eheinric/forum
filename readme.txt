// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file contains information about the forum plugin for assign/submission
 *
 *
 *
 * @package assignsubmission_forum
 * @copyright 2012 Massey University  {@link http://www.massey.ac.nz}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 
 Purpose of this plugin
 ______________________
 
 The idea behind this plugin is to help a teacher with the assessment of 
 forum contributions. The teacher sets up an assignment (which initially 
 might be hidden from students) and determines the forum from which to extract 
 student contributions. The teacher also sets the date at which  contributions 
 will be extracted.
 
 Once contributions are extracted (triggered by cron via the time set), 
 an html file is available for marking in the assignment. The teacher 
 can use all the usual assignment marking facilities.
 
 The advantage for the teacher is to see all contributions of a student close together 
 (with links to check out a posting in context, if required). The advantage for 
 students is that they not only get the mark and comments back, but also can see 
 their postings (which might make it easier to understand the mark and feedback received).
 
 This plugin goes back to research undertaken by Yang Yang as part of his Masters 
 studies at Massey University (supervised by Eva Heinrich (e.heinrich@massey.ac.nz) 
 and Elizabeth Kemp).
 
 
 Current Code
 ____________
 
 This is the first version of this code working with the assignment module in Moodle 2.3.
 Code review and testing is strongly recommended.
 
 
 Example of XML generated
 ________________________
 
 
 <?xml version="1.0" encoding="UTF-8"?>
<forum title="Discussions on technologies we use" user="Mike Pear">
  <discussion title="Discussion: I prefer email" address="http://localhost:8888/moodle/mod/forum/discuss.php?d=6">
    <post date="Friday, 31 August 2012, 9:15 am" desc="See post in context" 
          address="http://localhost:8888/moodle/mod/forum/discuss.php?d=6#p13" add="">
      <text>Hi Tim, </text>
      <text></text>
      <text>I still like to write letters with pen and paper. In fact, I am into scrap</text>
      <text>booking, using nice paper and art supplies. </text>
      <text></text>
      <text>You should try it sometime, it is lots of fun. </text>
      <text></text>
      <text>Yours, Mike</text>        
    </post>
  </discussion>
  <discussion title="Discussion: Technology today" address="http://localhost:8888/moodle/mod/forum/discuss.php?d=7">
    <post date="Friday, 31 August 2012, 9:22 am" desc="See post in context" 
          address="http://localhost:8888/moodle/mod/forum/discuss.php?d=7" add="Post has attachment">
      <text>Look at this cool cellphone, unbelievable!</text>        
    </post>
  </discussion>
  <discussion title="Discussion: Here you can find more cool stuff" 
        address="http://localhost:8888/moodle/mod/forum/discuss.php?d=8">
    <post date="Friday, 31 August 2012, 9:24 am" desc="See post in context" 
            address="http://localhost:8888/moodle/mod/forum/discuss.php?d=8" add="">
      <text>What about a new phone? </text>
      <text></text>
      <text>http://en.wikipedia.org/wiki/IPhone </text>
      <text></text>
      <text>Wikipedia link [2] </text>
      <text></text>
      <text>Cool stuff, what do you think? </text>
      <text></text>
      <text>Yours, Mike</text>
      <text></text>
      <text>Links:</text>
      <text>------</text>
      <text>[1] http://en.wikipedia.org/wiki/IPhone</text>
      <text></text>        
    </post>
  </discussion>
 </forum>
 
  
 
 
 Extension Ideas
 _______________

- Multiple forums: Instead of just being able to select one forum per assignment, 
it could be helpful for the teacher to be able to select multiple forums, when 
marking across these multiple forums is desired.

- Date restrictions: Setting a start and an end date for the extraction would allow 
the teacher to control the time periods for extraction. E.g., the teacher might want 
to select weeks 1 and 2 of the course separately.

- Statistics: One could calculate statistics (like number of postings, length of 
postings) on class and individual levels and include these in the files generated 
per students.

- Relationships: One could extract information on the relationships about postings 
(who has interacted with whom, who is at the centre of discussions, who has isolated 
postings) and include these in the files generated per students.

- Teacher postings: One could extract postings from teachers (and teaching assistants) 
and make these available to the teacher.

- File format: One could give the user the choice between file formats. Currently, 
html files are created. PDF might be better?

- Plagiarism checking: One could activate plagiarism checking for the generated files.



Known Issues
____________

Conversion of hyperlinks into footnotes:
This is done as part of converting html to text by Moodle's format_text_email() function. 
This works, but seems to put in wrong numbering, like in the following example:

What about a new phone? 

Wikipedia link [2] 

Cool stuff, what do you think? 

Yours, Mike

Links:
------
[1] http://en.wikipedia.org/wiki/IPhone

___

Text formatting is not preserved:
Formatting like bold, underscore, italics is transformed into uppercase or 
leading underscore by Moodle's format_text_email() function. This is probably ok 
(converting into pdf instead of html would allow preserving the original posting more closely).

___
