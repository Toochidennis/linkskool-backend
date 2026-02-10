<?php

namespace V3\App\Services\Explore\LessonData;

class SQuizzes
{
    public static function getQuizzes(): array
    {
        return [
            1 => [
                [
                    "explanation_text" => "Scratch is a visual programming language that allows users to create interactive stories, games, and animations using blocks.",
                    "question_text" => "What is Scratch primarily used for?",
                    "option_1_text" => "Writing long essays",
                    "option_2_text" => "Editing photos",
                    "option_3_text" => "Creating games and animations",
                    "option_4_text" => "Watching videos",
                    "answer" => 3
                ],
                [
                    "explanation_text" => "In Scratch, you build programs by dragging and connecting blocks together.",
                    "question_text" => "How do you create programs in Scratch?",
                    "option_1_text" => "Typing code in a terminal",
                    "option_2_text" => "Writing HTML",
                    "option_3_text" => "Connecting code blocks",
                    "option_4_text" => "Recording videos",
                    "answer" => 3
                ],
                [
                    "explanation_text" => "Scratch is especially designed for beginners and children to learn programming in a fun way.",
                    "question_text" => "Who is Scratch mainly designed for?",
                    "option_1_text" => "Professional hackers",
                    "option_2_text" => "Children and beginners",
                    "option_3_text" => "Graphic designers",
                    "option_4_text" => "Data analysts",
                    "answer" => 2
                ],
                [
                    "explanation_text" => "Sprites are the characters or objects that perform actions in Scratch.",
                    "question_text" => "What are 'Sprites' in Scratch?",
                    "option_1_text" => "Background music",
                    "option_2_text" => "Photos",
                    "option_3_text" => "Characters or objects",
                    "option_4_text" => "Sound effects",
                    "answer" => 3
                ],
                [
                    "explanation_text" => "You can change how a sprite looks by editing or selecting costumes.",
                    "question_text" => "How do you change the appearance of a sprite?",
                    "option_1_text" => "Using a photo editor",
                    "option_2_text" => "Editing its costume",
                    "option_3_text" => "Changing the background",
                    "option_4_text" => "Writing a script",
                    "answer" => 2
                ],
                [
                    "explanation_text" => "The Stage is where the action happens in a Scratch project.",
                    "question_text" => "What is the function of the Stage in Scratch?",
                    "option_1_text" => "To create music",
                    "option_2_text" => "To show videos",
                    "option_3_text" => "To display the action",
                    "option_4_text" => "To write text",
                    "answer" => 3
                ],
                [
                    "explanation_text" => "Motion blocks in Scratch are used to move sprites around the stage.",
                    "question_text" => "What are Motion blocks used for in Scratch?",
                    "option_1_text" => "To play music",
                    "option_2_text" => "To move sprites",
                    "option_3_text" => "To change colors",
                    "option_4_text" => "To write text",
                    "answer" => 2
                ],
                [
                    "explanation_text" => "Looks blocks are used to control how a sprite appears, like changing its costume or saying something.",
                    "question_text" => "Which type of block makes a sprite speak or change appearance?",
                    "option_1_text" => "Motion block",
                    "option_2_text" => "Sound block",
                    "option_3_text" => "Looks block",
                    "option_4_text" => "Events block",
                    "answer" => 3
                ],
                [
                    "explanation_text" => "Sound blocks let you add music, sounds, or record voice clips to a project.",
                    "question_text" => "What do Sound blocks do in Scratch?",
                    "option_1_text" => "Change background",
                    "option_2_text" => "Move the sprite",
                    "option_3_text" => "Add or play sounds",
                    "option_4_text" => "Zoom in",
                    "answer" => 3
                ],
                [
                    "explanation_text" => "The Events block, such as 'when green flag clicked', starts the script when clicked.",
                    "question_text" => "Which block starts the program in Scratch?",
                    "option_1_text" => "Looks block",
                    "option_2_text" => "Green flag event block",
                    "option_3_text" => "Costume block",
                    "option_4_text" => "Sound block",
                    "answer" => 2
                ]
            ],
            2 => [
                [
                    "explanation_text" => "Event-driven programming lets things happen when specific events like key presses or clicks occur.",
                    "question_text" => "What is event-driven programming?",
                    "option_1_text" => "A way to build houses",
                    "option_2_text" => "Making animations play automatically",
                    "option_3_text" => "Programming that responds to events like key presses",
                    "option_4_text" => "Writing only motion code",
                    "answer" => 3
                ],
                [
                    "explanation_text" => "Clicking the green flag is one way to start an event in Scratch.",
                    "question_text" => "Which of the following can start an event in Scratch?",
                    "option_1_text" => "Dragging the mouse",
                    "option_2_text" => "Clicking the green flag",
                    "option_3_text" => "Typing random letters",
                    "option_4_text" => "Scrolling the screen",
                    "answer" => 2
                ],
                [
                    "explanation_text" => "The 'when space key pressed' block lets you use the spacebar to start your project.",
                    "question_text" => "Which block lets your project start when you press the spacebar?",
                    "option_1_text" => "When green flag clicked",
                    "option_2_text" => "Say hello",
                    "option_3_text" => "When sprite clicked",
                    "option_4_text" => "When space key pressed",
                    "answer" => 4
                ],
                [
                    "explanation_text" => "The 'say hello' block is found under the Looks blocks and can be used when a sprite is clicked.",
                    "question_text" => "What does the sprite do when you use the 'say hello' block after clicking it?",
                    "option_1_text" => "It disappears",
                    "option_2_text" => "It moves",
                    "option_3_text" => "It says 'hello'",
                    "option_4_text" => "It changes color",
                    "answer" => 3
                ],
                [
                    "explanation_text" => "Animation is when something looks like it's moving by changing its costume, size, or color over time.",
                    "question_text" => "What is animation in Scratch?",
                    "option_1_text" => "Drawing pictures",
                    "option_2_text" => "Making a sprite look like it is moving",
                    "option_3_text" => "Adding music only",
                    "option_4_text" => "Typing messages",
                    "answer" => 2
                ],
                [
                    "explanation_text" => "To animate a sprite, you can change its costume, size, or color.",
                    "question_text" => "Which of the following is NOT a way to animate a sprite?",
                    "option_1_text" => "Changing costumes",
                    "option_2_text" => "Changing size",
                    "option_3_text" => "Changing color",
                    "option_4_text" => "Typing on the keyboard",
                    "answer" => 4
                ],
                [
                    "explanation_text" => "Using the 'next costume' block inside a forever loop creates a continuous animation.",
                    "question_text" => "What block makes a sprite switch to its next costume?",
                    "option_1_text" => "Say hello",
                    "option_2_text" => "Next costume",
                    "option_3_text" => "Start sound",
                    "option_4_text" => "Wait 1 second",
                    "answer" => 2
                ],
                [
                    "explanation_text" => "Adding sound to an animation makes it more fun and exciting.",
                    "question_text" => "Why would you add sound to a sprite animation?",
                    "option_1_text" => "To make it louder",
                    "option_2_text" => "To show more costumes",
                    "option_3_text" => "To make it more exciting",
                    "option_4_text" => "To stop it from moving",
                    "answer" => 3
                ],
                [
                    "explanation_text" => "The ballerina sprite was used in the example of dancing with a drum sound.",
                    "question_text" => "Which sprite was used to make a dance animation with a drum sound?",
                    "option_1_text" => "Avery Walking",
                    "option_2_text" => "Cat",
                    "option_3_text" => "Ballerina",
                    "option_4_text" => "Robot",
                    "answer" => 3
                ],
                [
                    "explanation_text" => "The backdrop chosen for the dance animation project was the Theater.",
                    "question_text" => "What backdrop was used in the ballerina dance animation?",
                    "option_1_text" => "Blue sky",
                    "option_2_text" => "Classroom",
                    "option_3_text" => "Theater",
                    "option_4_text" => "Park",
                    "answer" => 3
                ]
            ],
            3 => [
                [
                    "explanation_text" => "Loops in Scratch allow actions to repeat without manually duplicating blocks.",
                    "question_text" => "What is the purpose of loops in Scratch?",
                    "option_1_text" => "To make costumes",
                    "option_2_text" => "To create music",
                    "option_3_text" => "To repeat actions multiple times",
                    "option_4_text" => "To change the background",
                    "answer" => 3
                ],
                [
                    "explanation_text" => "The Forever Loop keeps repeating instructions endlessly as long as the project runs.",
                    "question_text" => "What does the Forever Loop do?",
                    "option_1_text" => "Repeats actions a set number of times",
                    "option_2_text" => "Ends the script",
                    "option_3_text" => "Runs actions one time only",
                    "option_4_text" => "Repeats actions endlessly",
                    "answer" => 4
                ],
                [
                    "explanation_text" => "The Repeat Loop in Scratch runs actions a specific number of times.",
                    "question_text" => "Which loop runs blocks a certain number of times?",
                    "option_1_text" => "Forever loop",
                    "option_2_text" => "If-Then loop",
                    "option_3_text" => "Repeat loop",
                    "option_4_text" => "Until loop",
                    "answer" => 3
                ],
                [
                    "explanation_text" => "The If-Then loop checks a condition and runs the action only if that condition is true.",
                    "question_text" => "What is the purpose of the If-Then loop in Scratch?",
                    "option_1_text" => "To repeat actions",
                    "option_2_text" => "To show animations",
                    "option_3_text" => "To run code only when a condition is true",
                    "option_4_text" => "To create new sprites",
                    "answer" => 3
                ],
                [
                    "explanation_text" => "In Scratch, sensing blocks like 'key pressed' are used to detect user input such as keyboard presses.",
                    "question_text" => "Which block checks if a key is pressed?",
                    "option_1_text" => "Motion",
                    "option_2_text" => "Control",
                    "option_3_text" => "Sensing",
                    "option_4_text" => "Looks",
                    "answer" => 3
                ],
                [
                    "explanation_text" => "The 'if on edge, bounce' block makes a sprite bounce back when it touches the edge of the stage.",
                    "question_text" => "What does the 'if on edge, bounce' block do?",
                    "option_1_text" => "Creates a sound",
                    "option_2_text" => "Stops the sprite",
                    "option_3_text" => "Changes the sprite's costume",
                    "option_4_text" => "Bounces the sprite off the screen edge",
                    "answer" => 4
                ],
                [
                    "explanation_text" => "The 'next costume' block changes the sprite's appearance to the next image in the costume list.",
                    "question_text" => "What does the 'next costume' block do in Scratch?",
                    "option_1_text" => "Adds a sound",
                    "option_2_text" => "Switches to the next costume",
                    "option_3_text" => "Moves the sprite",
                    "option_4_text" => "Changes the backdrop",
                    "answer" => 2
                ],
                [
                    "explanation_text" => "Using loops helps make Scratch projects easier to manage and keeps the code clean.",
                    "question_text" => "Why should we use loops in our Scratch projects?",
                    "option_1_text" => "To make sprites disappear",
                    "option_2_text" => "To draw backgrounds",
                    "option_3_text" => "To keep code organized and avoid repeating blocks",
                    "option_4_text" => "To delete blocks",
                    "answer" => 3
                ],
                [
                    "explanation_text" => "In the lesson, a ball sprite used a forever loop to keep moving and bouncing on the screen.",
                    "question_text" => "Which loop made the ball sprite move and bounce continuously?",
                    "option_1_text" => "Repeat loop",
                    "option_2_text" => "If-Then loop",
                    "option_3_text" => "Forever loop",
                    "option_4_text" => "Wait loop",
                    "answer" => 3
                ],
                [
                    "explanation_text" => "To move the sprite using arrow keys, the If-Then loop is combined with 'key pressed' and 'move' blocks.",
                    "question_text" => "Which blocks help a sprite move when arrow keys are pressed?",
                    "option_1_text" => "If-Then + key pressed + motion blocks",
                    "option_2_text" => "Looks + sound",
                    "option_3_text" => "Events + backdrop",
                    "option_4_text" => "Costume + sensing",
                    "answer" => 1
                ]
            ],
            4 => [
                [
                    "id" => 1,
                    "explanation_text" => "The 'Ask and Wait' block in Scratch allows the user to type an input like their name or number, and stores the answer.",
                    "question_text" => "Which block is used in Scratch to receive text input from the player?",
                    "option_1_text" => "Say block",
                    "option_2_text" => "Switch costume block",
                    "option_3_text" => "Ask and Wait block",
                    "option_4_text" => "Go to block",
                    "answer" => 3
                ],
                [
                    "id" => 2,
                    "explanation_text" => "The 'Answer' block stores the user's response from the Ask and Wait block and can be used in variables or other blocks.",
                    "question_text" => "Where is the user's input stored after using 'Ask and Wait'?",
                    "option_1_text" => "In the player block",
                    "option_2_text" => "In the answer block",
                    "option_3_text" => "In the motion block",
                    "option_4_text" => "In the stage block",
                    "answer" => 2
                ],
                [
                    "id" => 3,
                    "explanation_text" => "Sensing blocks allow the project to respond to events like mouse movement or touching colors or sprites.",
                    "question_text" => "What is the main purpose of sensing blocks in Scratch?",
                    "option_1_text" => "To change costumes",
                    "option_2_text" => "To detect player actions",
                    "option_3_text" => "To play music",
                    "option_4_text" => "To control loops",
                    "answer" => 2
                ],
                [
                    "id" => 4,
                    "explanation_text" => "The 'go to mouse pointer' block makes the sprite follow the mouse around the stage.",
                    "question_text" => "Which block is used to make a sprite follow the mouse pointer?",
                    "option_1_text" => "Move 10 steps",
                    "option_2_text" => "Point in direction",
                    "option_3_text" => "Go to mouse pointer",
                    "option_4_text" => "Glide to sprite",
                    "answer" => 3
                ],
                [
                    "id" => 5,
                    "explanation_text" => "The 'Touching Color' block lets a sprite detect when it comes in contact with a specific color.",
                    "question_text" => "Which block detects when a sprite touches a certain color?",
                    "option_1_text" => "Touching Sprite?",
                    "option_2_text" => "Touching Color?",
                    "option_3_text" => "If Then",
                    "option_4_text" => "Next Costume",
                    "answer" => 2
                ],
                [
                    "id" => 6,
                    "explanation_text" => "Using the 'Touching Color' block, we can make the sprite say something when it touches red, like 'Yey! I touched red.'",
                    "question_text" => "What can you use to make a sprite react when it touches red?",
                    "option_1_text" => "Say block only",
                    "option_2_text" => "Costume block",
                    "option_3_text" => "Touching Color + Say block",
                    "option_4_text" => "Backdrop block",
                    "answer" => 3
                ],
                [
                    "id" => 7,
                    "explanation_text" => "The 'Touching [Sprite]?' block is used to detect when two sprites collide or come into contact.",
                    "question_text" => "Which block is used to check if two sprites are touching?",
                    "option_1_text" => "Touching Color?",
                    "option_2_text" => "Touching Sprite?",
                    "option_3_text" => "Mouse X",
                    "option_4_text" => "Repeat Until",
                    "answer" => 2
                ],
                [
                    "id" => 8,
                    "explanation_text" => "You can duplicate a sprite in Scratch to create a second version, like Avery and Avery 2, for collision projects.",
                    "question_text" => "Why would you duplicate a sprite like Avery in a sensing project?",
                    "option_1_text" => "To change the stage",
                    "option_2_text" => "To detect when one sprite touches another",
                    "option_3_text" => "To create music",
                    "option_4_text" => "To delete a sprite",
                    "answer" => 2
                ],
                [
                    "id" => 9,
                    "explanation_text" => "The 'say' block is often used after detecting an action like touching a sprite or color to give feedback to the player.",
                    "question_text" => "What is commonly used to show a reaction after a sprite touches something?",
                    "option_1_text" => "Glide block",
                    "option_2_text" => "Say block",
                    "option_3_text" => "Move block",
                    "option_4_text" => "Play sound",
                    "answer" => 2
                ],
                [
                    "id" => 10,
                    "explanation_text" => "The 'forever' block keeps checking sensing conditions like touching sprite or color until the program stops.",
                    "question_text" => "Which block is best used to keep checking for interactions in Scratch?",
                    "option_1_text" => "If Then block",
                    "option_2_text" => "Repeat 10 block",
                    "option_3_text" => "Forever block",
                    "option_4_text" => "Wait block",
                    "answer" => 3
                ]
            ],
            5 => [
                [
                    "explanation_text" => "A variable in Scratch is like a box that can store numbers or words, which can be changed and used in projects.",
                    "question_text" => "What is a variable in Scratch?",
                    "option_1_text" => "A sound block",
                    "option_2_text" => "A control button",
                    "option_3_text" => "A storage container for values",
                    "option_4_text" => "An image sprite",
                    "answer" => 3
                ],
                [
                    "explanation_text" => "The 'when flag clicked' block is used to start a script when the green flag is clicked.",
                    "question_text" => "Which block is used to start code when the green flag is clicked?",
                    "option_1_text" => "When sprite clicked",
                    "option_2_text" => "When flag clicked",
                    "option_3_text" => "Broadcast start",
                    "option_4_text" => "Forever loop",
                    "answer" => 2
                ],
                [
                    "explanation_text" => "The 'set variable to' block initializes a variable to a specific value.",
                    "question_text" => "What does the 'set variable to' block do?",
                    "option_1_text" => "Deletes the variable",
                    "option_2_text" => "Shows the variable",
                    "option_3_text" => "Sets the variable to a specific value",
                    "option_4_text" => "Plays a sound",
                    "answer" => 3
                ],
                [
                    "explanation_text" => "The 'change variable by 1' block is used to increase the variable's value.",
                    "question_text" => "Which block increases a variable by a given value?",
                    "option_1_text" => "Set variable to",
                    "option_2_text" => "Change variable by",
                    "option_3_text" => "Wait block",
                    "option_4_text" => "Stop all",
                    "answer" => 2
                ],
                [
                    "explanation_text" => "A score variable is used to keep track of points in a game.",
                    "question_text" => "What is the purpose of a score variable in Scratch?",
                    "option_1_text" => "To store sound effects",
                    "option_2_text" => "To count how many times a player scores",
                    "option_3_text" => "To pause the game",
                    "option_4_text" => "To change the backdrop",
                    "answer" => 2
                ],
                [
                    "explanation_text" => "The 'ask and wait' block prompts the user with a question and waits for input.",
                    "question_text" => "What is the 'ask and wait' block used for?",
                    "option_1_text" => "To move a sprite",
                    "option_2_text" => "To display a backdrop",
                    "option_3_text" => "To ask a question and get user input",
                    "option_4_text" => "To set a variable",
                    "answer" => 3
                ],
                [
                    "explanation_text" => "The 'broadcast' block is used to send a signal that triggers scripts in other sprites.",
                    "question_text" => "What does the 'broadcast' block do in Scratch?",
                    "option_1_text" => "Plays music",
                    "option_2_text" => "Sends a message to trigger events",
                    "option_3_text" => "Displays a message box",
                    "option_4_text" => "Stops all scripts",
                    "answer" => 2
                ],
                [
                    "explanation_text" => "A variable timer lets you create a custom timer using variables instead of using Scratch's built-in timer.",
                    "question_text" => "What is a variable timer in Scratch used for?",
                    "option_1_text" => "To display the current date",
                    "option_2_text" => "To play a countdown sound",
                    "option_3_text" => "To control time using a variable",
                    "option_4_text" => "To stop the script",
                    "answer" => 3
                ],
                [
                    "explanation_text" => "The 'if then' block runs a section of code only if a certain condition is true.",
                    "question_text" => "What is the purpose of the 'if then' block?",
                    "option_1_text" => "To loop code forever",
                    "option_2_text" => "To stop all scripts",
                    "option_3_text" => "To ask the user a question",
                    "option_4_text" => "To run code based on a condition",
                    "answer" => 4
                ],
                [
                    "explanation_text" => "You can duplicate blocks to create multiple questions in your quiz game.",
                    "question_text" => "Why would you duplicate blocks in a quiz game?",
                    "option_1_text" => "To increase game speed",
                    "option_2_text" => "To add multiple questions",
                    "option_3_text" => "To stop the quiz",
                    "option_4_text" => "To remove a sprite",
                    "answer" => 2
                ]
            ],
            6 => [
                [
                    "id" => 1,
                    "explanation_text" => "Conditionals in Scratch are 'if-then' statements that allow a sprite to make decisions based on specific conditions.",
                    "question_text" => "What is a conditional in Scratch?",
                    "option_1_text" => "A loop that repeats forever",
                    "option_2_text" => "A function that plays music",
                    "option_3_text" => "A statement that checks conditions",
                    "option_4_text" => "A block that changes colors",
                    "answer" => 3
                ],
                [
                    "id" => 2,
                    "explanation_text" => "In the mini project, the hen says 'yummy' when it touches the bread using an 'if then' loop.",
                    "question_text" => "What happens when the hen touches the bread in the mini project?",
                    "option_1_text" => "It runs away",
                    "option_2_text" => "It disappears",
                    "option_3_text" => "It says 'yummy'",
                    "option_4_text" => "It stops moving",
                    "answer" => 3
                ],
                [
                    "id" => 3,
                    "explanation_text" => "The 'forever' block in Scratch repeats the code inside it forever.",
                    "question_text" => "What does the 'forever' block do in Scratch?",
                    "option_1_text" => "Stops the sprite",
                    "option_2_text" => "Runs the code once",
                    "option_3_text" => "Repeats the code until stopped",
                    "option_4_text" => "Deletes the sprite",
                    "answer" => 3
                ],
                [
                    "id" => 4,
                    "explanation_text" => "Simple AI in Scratch can be created by using logic blocks like 'if then else' to make decisions.",
                    "question_text" => "How can we create simple AI in Scratch?",
                    "option_1_text" => "By using sound blocks",
                    "option_2_text" => "By using 'if then else' logic",
                    "option_3_text" => "By using costumes only",
                    "option_4_text" => "By changing the stage",
                    "answer" => 2
                ],
                [
                    "id" => 5,
                    "explanation_text" => "The 'ask' block is used to get user input, like asking for a favorite color.",
                    "question_text" => "What is the 'ask' block used for in Scratch?",
                    "option_1_text" => "To change sprite color",
                    "option_2_text" => "To play music",
                    "option_3_text" => "To get user input",
                    "option_4_text" => "To create loops",
                    "answer" => 3
                ],
                [
                    "id" => 6,
                    "explanation_text" => "In the 'Hungry Hunter' game, the cat moves using arrow keys and looks for food while avoiding obstacles.",
                    "question_text" => "What is the goal of the 'Hungry Hunter' game?",
                    "option_1_text" => "To hide the mouse",
                    "option_2_text" => "To jump on platforms",
                    "option_3_text" => "To find food while avoiding obstacles",
                    "option_4_text" => "To change colors",
                    "answer" => 3
                ],
                [
                    "id" => 7,
                    "explanation_text" => "The 'touching color' block is used to detect when a sprite touches a specific color.",
                    "question_text" => "What is the purpose of the 'touching color' block?",
                    "option_1_text" => "To change the sprite's color",
                    "option_2_text" => "To detect collisions with a color",
                    "option_3_text" => "To play a song",
                    "option_4_text" => "To move the sprite",
                    "answer" => 2
                ],
                [
                    "id" => 8,
                    "explanation_text" => "The 'wait until' block pauses the script until a condition becomes true.",
                    "question_text" => "What does the 'wait until' block do?",
                    "option_1_text" => "Repeats forever",
                    "option_2_text" => "Skips the next block",
                    "option_3_text" => "Stops the project",
                    "option_4_text" => "Waits until a condition is true",
                    "answer" => 4
                ],
                [
                    "id" => 9,
                    "explanation_text" => "The 'say' block makes the sprite display a speech bubble with text.",
                    "question_text" => "What does the 'say' block do in Scratch?",
                    "option_1_text" => "Plays a sound",
                    "option_2_text" => "Shows a message from the sprite",
                    "option_3_text" => "Moves the sprite",
                    "option_4_text" => "Changes the sprite's size",
                    "answer" => 2
                ],
                [
                    "id" => 10,
                    "explanation_text" => "In the project, when the cat touches the mouse, the mouse hides and a sound plays.",
                    "question_text" => "What happens when the cat sprite touches the mouse in the game?",
                    "option_1_text" => "The cat disappears",
                    "option_2_text" => "The game restarts",
                    "option_3_text" => "The mouse hides and sound plays",
                    "option_4_text" => "Nothing happens",
                    "answer" => 3
                ]
            ],
            7 => [
                [
                    "id" => 1,
                    "explanation_text" => "Cloning means making an exact copy of something. In Scratch, we can clone sprites.",
                    "question_text" => "What does cloning mean in Scratch?",
                    "option_1_text" => "Deleting a sprite",
                    "option_2_text" => "Making a different version of a sprite",
                    "option_3_text" => "Making an exact copy of a sprite",
                    "option_4_text" => "Changing the name of a sprite",
                    "answer" => 3
                ],
                [
                    "id" => 2,
                    "explanation_text" => "In the mini project, we make a balloon create clones of itself with different colors and sizes.",
                    "question_text" => "What does the balloon sprite do in the mini project?",
                    "option_1_text" => "Disappear when clicked",
                    "option_2_text" => "Change the background",
                    "option_3_text" => "Create clones of itself with different colors and sizes",
                    "option_4_text" => "Play music",
                    "answer" => 3
                ],
                [
                    "id" => 3,
                    "explanation_text" => "The 'when this sprite is clicked' block from the Events category starts the cloning.",
                    "question_text" => "Which block is used to start cloning when the sprite is clicked?",
                    "option_1_text" => "When green flag clicked",
                    "option_2_text" => "When key pressed",
                    "option_3_text" => "When this sprite is clicked",
                    "option_4_text" => "Broadcast message",
                    "answer" => 3
                ],
                [
                    "id" => 4,
                    "explanation_text" => "The 'create clone of myself' block is used to generate a clone.",
                    "question_text" => "What block is used to generate a clone in Scratch?",
                    "option_1_text" => "Duplicate sprite",
                    "option_2_text" => "Create clone of myself",
                    "option_3_text" => "Copy costume",
                    "option_4_text" => "Add new sprite",
                    "answer" => 2
                ],
                [
                    "id" => 5,
                    "explanation_text" => "Cloned sprites can be set to go to random positions using the 'go to random position' motion block.",
                    "question_text" => "How do we make clones appear in different places?",
                    "option_1_text" => "Use the 'go to random position' block",
                    "option_2_text" => "Manually drag them",
                    "option_3_text" => "Rename each one",
                    "option_4_text" => "Use 'switch costume'",
                    "answer" => 1
                ],
                [
                    "id" => 6,
                    "explanation_text" => "The mini game 'Rain Catch' involves catching falling rain (crystals) with an arrow sprite.",
                    "question_text" => "What is the goal of the 'Rain Catch' game?",
                    "option_1_text" => "Collect stars",
                    "option_2_text" => "Catch rain using an arrow sprite",
                    "option_3_text" => "Avoid balloons",
                    "option_4_text" => "Pop bubbles",
                    "answer" => 2
                ],
                [
                    "id" => 7,
                    "explanation_text" => "In the Rain Catch game, clones are created and fall, and if they touch the arrow, the score increases.",
                    "question_text" => "What happens when a falling clone touches the arrow in 'Rain Catch'?",
                    "option_1_text" => "The game ends",
                    "option_2_text" => "A sound plays and the score increases",
                    "option_3_text" => "The clone gets bigger",
                    "option_4_text" => "The arrow disappears",
                    "answer" => 2
                ],
                [
                    "id" => 8,
                    "explanation_text" => "The 'when I start as a clone' block allows you to define what each clone does when it appears.",
                    "question_text" => "Which block is used to control what clones do after they are created?",
                    "option_1_text" => "Repeat until",
                    "option_2_text" => "Switch backdrop",
                    "option_3_text" => "When I start as a clone",
                    "option_4_text" => "Broadcast message",
                    "answer" => 3
                ],
                [
                    "id" => 9,
                    "explanation_text" => "The clones fall by changing their y position using the 'change y by' block in a loop.",
                    "question_text" => "Which block makes the clone fall in the Rain Catch game?",
                    "option_1_text" => "Change y by",
                    "option_2_text" => "Set size to",
                    "option_3_text" => "Say Hello",
                    "option_4_text" => "Go to x: y:",
                    "answer" => 1
                ],
                [
                    "id" => 10,
                    "explanation_text" => "To remove the clone after being caught, we use the 'delete this clone' block.",
                    "question_text" => "What block is used to remove a clone after it is caught?",
                    "option_1_text" => "Hide",
                    "option_2_text" => "Remove sprite",
                    "option_3_text" => "Delete this clone",
                    "option_4_text" => "Stop all",
                    "answer" => 3
                ]
            ],
            8 => [
                [
                    "id" => 1,
                    "explanation_text" => "The Falling Star Game uses a star and bowl sprite to create a fun point-based game.",
                    "question_text" => "Which two sprites are used in the Falling Star Game?",
                    "option_1_text" => "Car and Road",
                    "option_2_text" => "Star and Bowl",
                    "option_3_text" => "Dog and Bone",
                    "option_4_text" => "Bird and Tree",
                    "answer" => 2
                ],
                [
                    "id" => 2,
                    "explanation_text" => "You will use the bowl to catch stars and earn points.",
                    "question_text" => "What is the purpose of the bowl sprite in the game?",
                    "option_1_text" => "To bounce the star",
                    "option_2_text" => "To decorate the game",
                    "option_3_text" => "To catch falling stars",
                    "option_4_text" => "To make sounds",
                    "answer" => 3
                ],
                [
                    "id" => 3,
                    "explanation_text" => "You begin by selecting the star sprite from the sprite library.",
                    "question_text" => "What is the first sprite to add for this game?",
                    "option_1_text" => "Bowl",
                    "option_2_text" => "Ball",
                    "option_3_text" => "Star",
                    "option_4_text" => "Moon",
                    "answer" => 3
                ],
                [
                    "id" => 4,
                    "explanation_text" => "You draw a night view in the backdrop paint area for the background.",
                    "question_text" => "What kind of backdrop should be created?",
                    "option_1_text" => "Beach view",
                    "option_2_text" => "Night view",
                    "option_3_text" => "Sunny day",
                    "option_4_text" => "Underwater",
                    "answer" => 2
                ],
                [
                    "id" => 5,
                    "explanation_text" => "The 'when green flag clicked' block starts the code for the bowl.",
                    "question_text" => "Which block begins the bowl's code when the game starts?",
                    "option_1_text" => "When space key pressed",
                    "option_2_text" => "When sprite clicked",
                    "option_3_text" => "When green flag clicked",
                    "option_4_text" => "When backdrop switches to",
                    "answer" => 3
                ],
                [
                    "id" => 6,
                    "explanation_text" => "The arrow keys are used to move the bowl left and right.",
                    "question_text" => "How do you control the bowl sprite in the game?",
                    "option_1_text" => "Mouse click",
                    "option_2_text" => "Spacebar",
                    "option_3_text" => "Arrow keys",
                    "option_4_text" => "Dragging with the mouse",
                    "answer" => 3
                ],
                [
                    "id" => 7,
                    "explanation_text" => "The star sprite is programmed to fall using a 'change y by' block inside a 'forever' loop.",
                    "question_text" => "Which block causes the star to fall continuously?",
                    "option_1_text" => "Go to x: y:",
                    "option_2_text" => "Change x by 10",
                    "option_3_text" => "Change y by -10",
                    "option_4_text" => "Change y by 10 inside forever loop",
                    "answer" => 4
                ],
                [
                    "id" => 8,
                    "explanation_text" => "When the star touches the bowl, a sound plays and the score increases.",
                    "question_text" => "What happens when the star touches the bowl?",
                    "option_1_text" => "Game ends",
                    "option_2_text" => "Bowl disappears",
                    "option_3_text" => "A sound plays and score increases",
                    "option_4_text" => "Star turns red",
                    "answer" => 3
                ],
                [
                    "id" => 9,
                    "explanation_text" => "A second star with a different color is added to increase difficulty.",
                    "question_text" => "Why is a second colored star added?",
                    "option_1_text" => "To decorate the background",
                    "option_2_text" => "To make the game easier",
                    "option_3_text" => "To distract the player",
                    "option_4_text" => "To add challenge with multiple falling stars",
                    "answer" => 4
                ],
                [
                    "id" => 10,
                    "explanation_text" => "The green flag is used to start the game and allow keyboard play.",
                    "question_text" => "What do you click to start the game?",
                    "option_1_text" => "The star sprite",
                    "option_2_text" => "The bowl sprite",
                    "option_3_text" => "The green flag",
                    "option_4_text" => "The backdrop",
                    "answer" => 3
                ]
            ],
            9 => [
                [
                    "id" => 1,
                    "explanation_text" => "Gravity in Scratch is like making your character fall, similar to dropping a ball in real life.",
                    "question_text" => "What does gravity do in Scratch?",
                    "option_1_text" => "Makes the character fly",
                    "option_2_text" => "Makes the character fall like a ball",
                    "option_3_text" => "Stops the character",
                    "option_4_text" => "Makes the character disappear",
                    "answer" => 2
                ],
                [
                    "id" => 2,
                    "explanation_text" => "When the space bar is pressed, the character jumps, and gravity pulls it back down.",
                    "question_text" => "What happens when you press the space bar in the gravity game?",
                    "option_1_text" => "The character spins",
                    "option_2_text" => "The character jumps",
                    "option_3_text" => "The game stops",
                    "option_4_text" => "The backdrop changes",
                    "answer" => 2
                ],
                [
                    "id" => 3,
                    "explanation_text" => "The game created is called 'Unicorn Glide' where the unicorn jumps over obstacles.",
                    "question_text" => "What is the name of the Scratch game created in this lesson?",
                    "option_1_text" => "Jumping Unicorn",
                    "option_2_text" => "Unicorn Escape",
                    "option_3_text" => "Unicorn Glide",
                    "option_4_text" => "Rainbow Run",
                    "answer" => 3
                ],
                [
                    "id" => 4,
                    "explanation_text" => "To begin the project, the sprite icon is hovered over and a new backdrop is painted.",
                    "question_text" => "How do you start designing your backdrop in the project?",
                    "option_1_text" => "Click on Backdrop Library",
                    "option_2_text" => "Hover over sprite and click 'paint'",
                    "option_3_text" => "Import from file",
                    "option_4_text" => "Duplicate a sprite",
                    "answer" => 2
                ],
                [
                    "id" => 5,
                    "explanation_text" => "The 'when green flag clicked' block is used to begin scripts in Scratch.",
                    "question_text" => "Which event block starts your Scratch script?",
                    "option_1_text" => "When space key pressed",
                    "option_2_text" => "Forever loop",
                    "option_3_text" => "When green flag clicked",
                    "option_4_text" => "Repeat until",
                    "answer" => 3
                ],
                [
                    "id" => 6,
                    "explanation_text" => "To animate movement, the 'change x by' and 'go to x:y' motion blocks are used.",
                    "question_text" => "Which block changes the sprite's position horizontally?",
                    "option_1_text" => "Change y by",
                    "option_2_text" => "Glide 1 secs to x:y",
                    "option_3_text" => "Go to front layer",
                    "option_4_text" => "Change x by",
                    "answer" => 4
                ],
                [
                    "id" => 7,
                    "explanation_text" => "In the unicorn jumping code, 'repeat 10' and 'change y by 10' are used for the jump effect.",
                    "question_text" => "What combination is used to make the unicorn jump up?",
                    "option_1_text" => "Repeat 5 and change y by -10",
                    "option_2_text" => "Repeat 10 and change y by 10",
                    "option_3_text" => "Forever loop and change x by 10",
                    "option_4_text" => "Repeat until touching edge",
                    "answer" => 2
                ],
                [
                    "id" => 8,
                    "explanation_text" => "To track score, a variable is created and set to 0 at the beginning of the project.",
                    "question_text" => "How do you initialize the score variable?",
                    "option_1_text" => "Set score to 10",
                    "option_2_text" => "Hide variable",
                    "option_3_text" => "Set score to 0",
                    "option_4_text" => "Change score by 1",
                    "answer" => 3
                ],
                [
                    "id" => 9,
                    "explanation_text" => "Rocks are added as obstacles using a new sprite and coded to move with motion and glide blocks.",
                    "question_text" => "What is the purpose of the 'Rock' sprite in the game?",
                    "option_1_text" => "It helps the unicorn fly",
                    "option_2_text" => "It changes the costume",
                    "option_3_text" => "It is an obstacle to jump over",
                    "option_4_text" => "It plays background music",
                    "answer" => 3
                ],
                [
                    "id" => 10,
                    "explanation_text" => "To detect collisions, the 'touching sprite' block is used with 'stop all' to end the game.",
                    "question_text" => "Which block is used to stop the game when a collision happens?",
                    "option_1_text" => "Glide 1 secs to x:y",
                    "option_2_text" => "Repeat 10",
                    "option_3_text" => "Touching sprite + Stop all",
                    "option_4_text" => "When flag clicked",
                    "answer" => 3
                ]
            ],
            1 => [
                [
                    "id" => 1,
                    "explanation_text" => "In Scratch, stories and animations use multiple backdrops, characters, and movements to simulate action.",
                    "question_text" => "What is used in Scratch to create stories and animations?",
                    "option_1_text" => "Only sound effects",
                    "option_2_text" => "Multiple sprites and backdrops",
                    "option_3_text" => "Code only",
                    "option_4_text" => "Text boxes",
                    "answer" => 2
                ],
                [
                    "id" => 2,
                    "explanation_text" => "The 'say' block allows a sprite to show speech bubbles, making it look like characters are talking.",
                    "question_text" => "Which block is used to make a character talk in Scratch?",
                    "option_1_text" => "move",
                    "option_2_text" => "when clicked",
                    "option_3_text" => "say",
                    "option_4_text" => "hide",
                    "answer" => 3
                ],
                [
                    "id" => 3,
                    "explanation_text" => "The 'switch backdrop to' block is used to change the background scene during a story.",
                    "question_text" => "How do you change scenes in a Scratch animation?",
                    "option_1_text" => "Use 'next costume'",
                    "option_2_text" => "Use 'switch backdrop to'",
                    "option_3_text" => "Use 'go to x y'",
                    "option_4_text" => "Use 'broadcast'",
                    "answer" => 2
                ],
                [
                    "id" => 4,
                    "explanation_text" => "Broadcast blocks let different sprites respond to events and act together in sequence.",
                    "question_text" => "Why is the 'broadcast' block important in Scratch stories?",
                    "option_1_text" => "It adds color to the background",
                    "option_2_text" => "It helps organize scripts",
                    "option_3_text" => "It allows multiple sprites to act in sequence",
                    "option_4_text" => "It saves the project",
                    "answer" => 3
                ],
                [
                    "id" => 5,
                    "explanation_text" => "Costumes are used to change a sprite's appearance for actions like walking or expressing emotions.",
                    "question_text" => "What are 'costumes' used for in Scratch animations?",
                    "option_1_text" => "To play music",
                    "option_2_text" => "To write text",
                    "option_3_text" => "To change a sprite's look or pose",
                    "option_4_text" => "To load a new project",
                    "answer" => 3
                ],
                [
                    "id" => 6,
                    "explanation_text" => "Adding timing using 'wait' blocks helps control the flow of dialogue or actions.",
                    "question_text" => "What is the purpose of the 'wait' block in a story animation?",
                    "option_1_text" => "To change the volume",
                    "option_2_text" => "To delay actions for better timing",
                    "option_3_text" => "To delete characters",
                    "option_4_text" => "To increase speed",
                    "answer" => 2
                ],
                [
                    "id" => 7,
                    "explanation_text" => "Each sprite can have its own script to control its behavior and interaction in the story.",
                    "question_text" => "What can each sprite in Scratch have to control its actions?",
                    "option_1_text" => "A video file",
                    "option_2_text" => "A music playlist",
                    "option_3_text" => "Its own script",
                    "option_4_text" => "Only a costume",
                    "answer" => 3
                ],
                [
                    "id" => 8,
                    "explanation_text" => "The green flag is used to start the story or animation.",
                    "question_text" => "How do you start running your story animation in Scratch?",
                    "option_1_text" => "Click the red stop sign",
                    "option_2_text" => "Click the green flag",
                    "option_3_text" => "Double click the backdrop",
                    "option_4_text" => "Right-click the sprite",
                    "answer" => 2
                ],
                [
                    "id" => 9,
                    "explanation_text" => "Good animation storytelling includes characters, dialogue, scenes, and actions.",
                    "question_text" => "Which of the following is essential for Scratch story animation?",
                    "option_1_text" => "Only a backdrop",
                    "option_2_text" => "Only music",
                    "option_3_text" => "Characters, dialogue, and actions",
                    "option_4_text" => "Only costumes",
                    "answer" => 3
                ],
                [
                    "id" => 10,
                    "explanation_text" => "To make characters act in order, you can use 'broadcast' and 'when I receive' blocks.",
                    "question_text" => "Which blocks help make characters perform actions one after another?",
                    "option_1_text" => "'go to' and 'wait'",
                    "option_2_text" => "'switch costume' and 'hide'",
                    "option_3_text" => "'broadcast' and 'when I receive'",
                    "option_4_text" => "'repeat' and 'forever'",
                    "answer" => 3
                ]
            ],
            11 => [
                [
                    "id" => 1,
                    "explanation_text" => "Scratch Pong is a game similar to table tennis where you bounce a ball using a paddle to keep it from falling.",
                    "question_text" => "What is Scratch Pong?",
                    "option_1_text" => "A music game in Scratch",
                    "option_2_text" => "A typing game for kids",
                    "option_3_text" => "A ping pong-like game made in Scratch",
                    "option_4_text" => "A math puzzle game",
                    "answer" => 3
                ],
                [
                    "id" => 2,
                    "explanation_text" => "The ball in Scratch Pong bounces off the paddle and walls, and you lose if it falls off the screen.",
                    "question_text" => "What happens when the ball hits the paddle in Scratch Pong?",
                    "option_1_text" => "It disappears",
                    "option_2_text" => "It changes color",
                    "option_3_text" => "It bounces back",
                    "option_4_text" => "It gets bigger",
                    "answer" => 3
                ],
                [
                    "id" => 3,
                    "explanation_text" => "The paddle in the game is moved using the x-position of the mouse.",
                    "question_text" => "How is the paddle moved in the Scratch Pong game?",
                    "option_1_text" => "By arrow keys",
                    "option_2_text" => "By typing commands",
                    "option_3_text" => "By mouse x position",
                    "option_4_text" => "By microphone input",
                    "answer" => 3
                ],
                [
                    "id" => 4,
                    "explanation_text" => "You start coding the paddle by using the 'when green flag clicked' block from Events.",
                    "question_text" => "Which block do you use first when coding the paddle?",
                    "option_1_text" => "When space key pressed",
                    "option_2_text" => "When green flag clicked",
                    "option_3_text" => "Wait 1 second",
                    "option_4_text" => "Repeat until",
                    "answer" => 2
                ],
                [
                    "id" => 5,
                    "explanation_text" => "The 'forever' loop is used to keep the paddle moving with the mouse continuously.",
                    "question_text" => "Why is the 'forever' block used in coding the paddle?",
                    "option_1_text" => "To bounce the ball",
                    "option_2_text" => "To keep paddle visible",
                    "option_3_text" => "To continuously update paddle position",
                    "option_4_text" => "To stop the game",
                    "answer" => 3
                ],
                [
                    "id" => 6,
                    "explanation_text" => "The 'touching sprite' block is used in the line's code to detect when the ball hits it.",
                    "question_text" => "What is the purpose of the 'touching sprite' block in the line's code?",
                    "option_1_text" => "To move the paddle",
                    "option_2_text" => "To change the backdrop",
                    "option_3_text" => "To detect collisions",
                    "option_4_text" => "To rotate the sprite",
                    "answer" => 3
                ],
                [
                    "id" => 7,
                    "explanation_text" => "The game starts using the 'when green flag clicked' event block for all sprites.",
                    "question_text" => "What block starts the game in Scratch Pong?",
                    "option_1_text" => "When key pressed",
                    "option_2_text" => "When clicked",
                    "option_3_text" => "When green flag clicked",
                    "option_4_text" => "Start broadcast",
                    "answer" => 3
                ],
                [
                    "id" => 8,
                    "explanation_text" => "The ball moves and bounces using motion blocks like 'move 10 steps' and 'if on edge, bounce'.",
                    "question_text" => "Which blocks help the ball move and bounce?",
                    "option_1_text" => "Looks and sensing",
                    "option_2_text" => "Motion and control",
                    "option_3_text" => "Variable and data",
                    "option_4_text" => "Sound and events",
                    "answer" => 2
                ],
                [
                    "id" => 9,
                    "explanation_text" => "The game increases the score using the 'change variable by 1' block when the paddle hits the ball.",
                    "question_text" => "How is the score increased in the game?",
                    "option_1_text" => "When the paddle is clicked",
                    "option_2_text" => "When the backdrop changes",
                    "option_3_text" => "When the ball hits the paddle",
                    "option_4_text" => "When the game starts",
                    "answer" => 3
                ],
                [
                    "id" => 10,
                    "explanation_text" => "The 'stop all' block is used when the ball touches the line to end the game.",
                    "question_text" => "What happens when the ball touches the line?",
                    "option_1_text" => "The game restarts",
                    "option_2_text" => "A sound plays",
                    "option_3_text" => "The game says hello",
                    "option_4_text" => "The game stops",
                    "answer" => 4
                ]
            ],
            12 => [
                [
                    "id" => 1,
                    "explanation_text" => "Background music sets the mood and keeps players engaged while playing a game.",
                    "question_text" => "Why is background music important in games?",
                    "option_1_text" => "It makes the game load faster",
                    "option_2_text" => "It helps the character fly",
                    "option_3_text" => "It creates the mood and makes the game fun",
                    "option_4_text" => "It changes the graphics",
                    "answer" => 3
                ],
                [
                    "id" => 2,
                    "explanation_text" => "A happy tune can make a game feel cheerful, setting the right tone for players.",
                    "question_text" => "What kind of mood can a happy background tune set?",
                    "option_1_text" => "Sad",
                    "option_2_text" => "Cheerful",
                    "option_3_text" => "Scary",
                    "option_4_text" => "Angry",
                    "answer" => 2
                ],
                [
                    "id" => 3,
                    "explanation_text" => "In Scratch, the 'forever' block is used to make actions repeat continuously.",
                    "question_text" => "What does the 'forever' block do in Scratch?",
                    "option_1_text" => "Ends the program",
                    "option_2_text" => "Plays music once",
                    "option_3_text" => "Repeats actions continuously",
                    "option_4_text" => "Stops sound",
                    "answer" => 3
                ],
                [
                    "id" => 4,
                    "explanation_text" => "The 'change y by' block moves the sprite up or down.",
                    "question_text" => "What does the 'change y by' block do?",
                    "option_1_text" => "Moves the sprite left or right",
                    "option_2_text" => "Changes the costume",
                    "option_3_text" => "Moves the sprite up or down",
                    "option_4_text" => "Stops the game",
                    "answer" => 3
                ],
                [
                    "id" => 5,
                    "explanation_text" => "The 'when green flag clicked' block starts the script when the game starts.",
                    "question_text" => "What does 'when green flag clicked' block do in Scratch?",
                    "option_1_text" => "Stops the music",
                    "option_2_text" => "Changes the background",
                    "option_3_text" => "Starts the script",
                    "option_4_text" => "Ends the game",
                    "answer" => 3
                ],
                [
                    "id" => 6,
                    "explanation_text" => "To create a flying game, you start by selecting a 'cat flying' sprite.",
                    "question_text" => "Which sprite should you choose first when creating the flying cat game?",
                    "option_1_text" => "Crystal",
                    "option_2_text" => "Tree 1",
                    "option_3_text" => "Cat flying",
                    "option_4_text" => "Blue sky",
                    "answer" => 3
                ],
                [
                    "id" => 7,
                    "explanation_text" => "You need to use 'touching sprite' to detect collisions with other sprites.",
                    "question_text" => "Which block is used to detect if a sprite touches another sprite?",
                    "option_1_text" => "Forever",
                    "option_2_text" => "Touching sprite",
                    "option_3_text" => "Go to x: y",
                    "option_4_text" => "Play sound till done",
                    "answer" => 2
                ],
                [
                    "id" => 8,
                    "explanation_text" => "The 'set score to 0' block resets the score at the beginning of the game.",
                    "question_text" => "Why do we use 'set score to 0' at the start of the game?",
                    "option_1_text" => "To increase score",
                    "option_2_text" => "To end the game",
                    "option_3_text" => "To reset score",
                    "option_4_text" => "To pause the game",
                    "answer" => 3
                ],
                [
                    "id" => 9,
                    "explanation_text" => "The 'play sound till done' block plays a sound and waits for it to finish before moving on.",
                    "question_text" => "What does the 'play sound till done' block do?",
                    "option_1_text" => "Loops the sound forever",
                    "option_2_text" => "Stops all sounds",
                    "option_3_text" => "Plays sound and waits till it's finished",
                    "option_4_text" => "Increases volume",
                    "answer" => 3
                ],
                [
                    "id" => 10,
                    "explanation_text" => "The 'next costume' block changes the sprite's appearance to the next one in its list.",
                    "question_text" => "What happens when you use the 'next costume' block?",
                    "option_1_text" => "The game ends",
                    "option_2_text" => "The background changes",
                    "option_3_text" => "The sprite disappears",
                    "option_4_text" => "The sprite changes its appearance",
                    "answer" => 4
                ]
            ],
            13 => [
                [
                    "id" => 1,
                    "explanation_text" => "In Scratch, the green flag is clicked to start running your project.",
                    "question_text" => "How do you start your Scratch project?",
                    "option_1_text" => "Press Enter",
                    "option_2_text" => "Click on the sprite",
                    "option_3_text" => "Click the green flag",
                    "option_4_text" => "Double-click the stage",
                    "answer" => 3
                ],
                [
                    "id" => 2,
                    "explanation_text" => "Costumes allow sprites to change how they look during a project.",
                    "question_text" => "What are costumes used for in Scratch?",
                    "option_1_text" => "Play sounds",
                    "option_2_text" => "Change the sprite's appearance",
                    "option_3_text" => "Move the stage",
                    "option_4_text" => "Add backgrounds",
                    "answer" => 2
                ],
                [
                    "id" => 3,
                    "explanation_text" => "Broadcasting is how sprites communicate and trigger each other's actions.",
                    "question_text" => "What does the broadcast block help with?",
                    "option_1_text" => "Play music",
                    "option_2_text" => "Send messages between sprites",
                    "option_3_text" => "Change costumes",
                    "option_4_text" => "Move sprites randomly",
                    "answer" => 2
                ],
                [
                    "id" => 4,
                    "explanation_text" => "A clone is a copy of a sprite that acts like the original.",
                    "question_text" => "What is a clone in Scratch?",
                    "option_1_text" => "A costume change",
                    "option_2_text" => "A duplicate of a sprite",
                    "option_3_text" => "A new background",
                    "option_4_text" => "A sound effect",
                    "answer" => 2
                ],
                [
                    "id" => 5,
                    "explanation_text" => "The forever block repeats actions without stopping.",
                    "question_text" => "Which block keeps repeating actions forever?",
                    "option_1_text" => "Repeat 10",
                    "option_2_text" => "Wait 1 second",
                    "option_3_text" => "Forever",
                    "option_4_text" => "Stop all",
                    "answer" => 3
                ],
                [
                    "id" => 6,
                    "explanation_text" => "Conditionals like 'if then' make decisions during a project.",
                    "question_text" => "Which block is an example of a conditional?",
                    "option_1_text" => "Move 10 steps",
                    "option_2_text" => "If touching edge then",
                    "option_3_text" => "Repeat until",
                    "option_4_text" => "Set x to 0",
                    "answer" => 2
                ],
                [
                    "id" => 7,
                    "explanation_text" => "Gravity can be simulated by changing y values in Scratch.",
                    "question_text" => "How is gravity created in Scratch?",
                    "option_1_text" => "Change x by 10",
                    "option_2_text" => "Play sound",
                    "option_3_text" => "Change y by a negative number",
                    "option_4_text" => "Broadcast message",
                    "answer" => 3
                ],
                [
                    "id" => 8,
                    "explanation_text" => "The glide block moves a sprite smoothly from one position to another.",
                    "question_text" => "What does the 'glide' block do?",
                    "option_1_text" => "Switch costumes",
                    "option_2_text" => "Move smoothly to a position",
                    "option_3_text" => "Play music",
                    "option_4_text" => "Change backdrop",
                    "answer" => 2
                ],
                [
                    "id" => 9,
                    "explanation_text" => "The 'wait' block is important for timing actions like conversations or animations.",
                    "question_text" => "Which block makes the sprite pause before doing the next thing?",
                    "option_1_text" => "Repeat",
                    "option_2_text" => "Wait",
                    "option_3_text" => "Forever",
                    "option_4_text" => "Stop all",
                    "answer" => 2
                ],
                [
                    "id" => 10,
                    "explanation_text" => "The 'say' block displays text in a speech bubble above a sprite.",
                    "question_text" => "What happens when you use the 'say' block?",
                    "option_1_text" => "The sprite plays music",
                    "option_2_text" => "The sprite moves faster",
                    "option_3_text" => "The sprite shows a speech bubble",
                    "option_4_text" => "The sprite disappears",
                    "answer" => 3
                ]
            ],

        ];
    }
}
