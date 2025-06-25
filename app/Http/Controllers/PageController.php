<?php

	namespace App\Http\Controllers;

	use Illuminate\Http\Request;

	class PageController extends Controller
	{
		/**
		 * Display the home page.
		 *
		 * @return \Illuminate\View\View
		 */
		public function index()
		{
			$features = [
				'Core' => [
					'description' => 'The essential tools to get you started and keep your writing organized.',
					'items' => [
						['title' => 'Novel Import', 'description' => 'Bring your work into Novelcrafter as Word, Markdown or HTML files.', 'image' => 'import.webp'],
						['title' => 'Export', 'description' => 'Release your work into the wild, or keep it safe on your hard drive.', 'image' => 'export.webp'],
						['title' => 'Dark Mode', 'description' => 'Work at night, reduce eye strain or save your battery life. It also looks cool.', 'image' => 'dark-mode.webp'],
						['title' => 'Extract from Snippets', 'description' => 'Quickly extract codex entries and scenes from prior notes or articles.', 'image' => 'extract.webp'],
						['title' => 'Mobile Compatible', 'description' => 'Jot down ideas or review your story when you’re on the go.', 'image' => 'mobile.webp'],
						['title' => 'Novel Covers', 'description' => 'Make your bookshelf look (very) pretty with some artwork.', 'image' => 'covers.webp'],
						['title' => 'Novel POV', 'description' => 'Writing 1st person or 3rd person? Who’s the main character of your story?', 'image' => 'pov.webp'],
						['title' => 'Novel Templates', 'description' => 'Quick-start new (bestselling) ideas with personal novel templates.', 'image' => 'templates.webp'],
						['title' => 'Novel Tense', 'description' => 'Whether you write in present or past tense, we got you covered.', 'image' => 'tense.webp'],
						['title' => 'Pen Names', 'description' => 'Separate and group your novels by identity with pen names.', 'image' => 'pen-name.webp'],
						['title' => 'Revision History', 'description' => 'Turn back time to see what got changed, and restore previous versions.', 'image' => 'revision.webp'],
						['title' => 'Series', 'description' => 'Share your Codex to save you from re-entering information again.', 'image' => 'series.webp'],
						['title' => 'Snippets', 'description' => 'Keep notes, To-Dos and other small pieces of text to come back to later.', 'image' => 'snippets.webp'],
						['title' => 'Split/Pin panels', 'description' => 'Have a chat and your manuscript open at the same time for easy reference.', 'image' => 'split-pin.webp'],
					],
				],
				'Manuscript' => [
					'description' => 'Craft your story with a powerful and customizable writing environment.',
					'items' => [
						['title' => 'Rich Text Editor', 'description' => 'Bold, italic, underline, lists, headings, quotes,…', 'image' => 'editor.webp'],
						['title' => 'Marker/Highlighter', 'description' => 'Never lose a note to self again, or forget to follow up on a plot hole.', 'image' => 'marker.webp'],
						['title' => 'Customizable Interface', 'description' => 'Change spacing, switch to a dyslexia-friendly font, text size and more.', 'image' => 'interface.webp'],
						['title' => 'Focus Mode', 'description' => 'Go distraction-free and fullscreen. Just you and your words.', 'image' => 'focus.webp'],
						['title' => 'Marker Timeline', 'description' => 'Jump around, never miss a highlight and see scene lengths in proportion.', 'image' => 'timeline.webp'],
						['title' => 'Sections', 'description' => 'Segment alternative ideas, notes or topics in your manuscript.', 'image' => 'sections.webp'],
					],
				],
				'Planning' => [
					'description' => 'Structure your narrative, track plotlines, and visualize your story\'s architecture.',
					'items' => [
						['title' => 'Grid', 'description' => 'Drag and drop your acts, chapters and scenes into the right order.', 'image' => 'grid.webp'],
						['title' => 'Matrix', 'description' => 'A magical spreadsheet on steroids. View your entire story at a glance.', 'image' => 'matrix.webp'],
						['title' => 'Scene Labels', 'description' => 'Keep track of drafts or already finished scenes with custom labels.', 'image' => 'labels.webp'],
						['title' => 'Custom POVs', 'description' => 'Keep track of your his/hers romance, heist novels, or a multi-POV epic.', 'image' => 'custom-povs.webp'],
						['title' => 'Manual References', 'description' => 'Emphasize specific key locations, timelines and plot points.', 'image' => 'reference.webp'],
						['title' => 'Outline', 'description' => 'The no-frills, single-page view of your novel outline.', 'image' => 'outline.webp'],
						['title' => 'Scene Archive', 'description' => 'Don’t abandon your scenes just yet. Keep them in a safe place.', 'image' => 'scene.webp'],
						['title' => 'Subtitles', 'description' => 'Add time/location shifts or other notes to readers.', 'image' => 'subtitle.webp'],
						['title' => 'Exclude from AI', 'description' => 'Don’t confuse the AI with unrelated bits, or hide specific NSFW content.', 'image' => 'exclude-ai.webp'],
					],
				],
				'Codex' => [
					'description' => 'Build and manage your story\'s universe, from characters to lore, all in one place.',
					'items' => [
						['title' => 'Thumbnail', 'description' => 'Easily recognize your characters and locations with a thumbnail.', 'image' => 'thumbnails.webp'],
						['title' => 'Custom Details', 'description' => 'Databases gone wild, but better. Track story roles, species and more.', 'image' => 'custom-details.webp'],
						['title' => 'Mentions', 'description' => 'Quickly find all the occurences in scenes, chats, notes and more.', 'image' => 'mentions.webp'],
						['title' => 'Aliases/Nicknames', 'description' => 'Things don’t just have one name. Track all the alter-egos, nicknames and slang.', 'image' => 'nicknames.webp'],
						['title' => 'Colors/Highlighting', 'description' => 'Let prominent entries stand out, and highlight overused phrases.', 'image' => 'highlighting.webp'],
						['title' => 'Tagging', 'description' => 'Organize your entries with tags.', 'image' => 'tagging.webp'],
						['title' => 'Custom Categories', 'description' => 'Declutter and organize your personal wiki based on Tags.', 'image' => 'categories.webp'],
						['title' => 'Mention Timeline', 'description' => 'Track where things come and go, or are gossiped about in your story.', 'image' => 'mention-timeline.webp'],
						['title' => 'Progressions', 'description' => 'Things evolve over time. So why not your Codex, too?', 'image' => 'progression.webp'],
						['title' => 'Relations', 'description' => 'Build a network for families, organizations, or a secret cult.', 'image' => 'relations.webp'],
						['title' => 'Tracking', 'description' => 'Specify how to match entries and control the AI context.', 'image' => 'tracking.webp'],
					],
				],
				'AI' => [
					'description' => 'Leverage the power of AI to brainstorm, summarize, and enhance your writing.',
					'items' => [
						['title' => 'Text Replacement', 'description' => 'Run an AI prompt on selected bits of text.', 'image' => 'text-replacement.webp'],
						['title' => 'Scene Summarization', 'description' => 'Summarize your hard work in one click for easy reference.', 'image' => 'summary.webp'],
						['title' => 'Scene Beats', 'description' => 'Stay in the director’s seat by telling the AI what should happen next.', 'image' => 'beats.webp'],
						['title' => 'Character Detection', 'description' => 'Quickly populate your Codex with all the characters in a scene.', 'image' => 'character-detection.webp'],
						['title' => 'AI Connections', 'description' => 'Connect to a wide range of AI services for maximum flexibility and cost control.', 'image' => 'ai-connection.webp'],
						['title' => 'Custom Prompts', 'description' => 'Tailor any AI feature to your liking, down to the smallest detail.', 'image' => 'prompts.webp'],
						['title' => 'Customizable Parameters', 'description' => 'Tweak a model’s settings to get the result you want.', 'image' => 'parameters.webp'],
						['title' => 'Local AI Support', 'description' => 'Privacy-focused minds can rest easy (and save on cost).', 'image' => 'local-ai.webp'],
						['title' => 'Prompt Components', 'description' => 'Build your perfect prompt using modular, reusable pieces.', 'image' => 'prompt-components.webp'],
						['title' => 'Prompt Preview', 'description' => 'See the exact prompt as it is sent to the AI, or copy it to use in other tools.', 'image' => 'prompt-preview.webp'],
					],
				],
				'Chat' => [
					'description' => 'Converse with your story, get feedback, and brainstorm ideas in an interactive chat.',
					'items' => [
						['title' => 'Chat with your Scene', 'description' => 'Get feedback, brainstorm, and tinker with your scenes.', 'image' => 'chat-scene.webp'],
						['title' => 'Context Options', 'description' => 'Decide when to include your outline, a specific scene or the full novel.', 'image' => 'context.webp'],
						['title' => 'Extract from Chat', 'description' => 'Detect scene beats, codex entries or outlines to reduce copy-paste.', 'image' => 'extract-chat.webp'],
						['title' => 'Model/Prompt Switching', 'description' => 'Change who you talk to mid-conversation to keep the flow going.', 'image' => 'model-switching.webp'],
					],
				],
				'Review' => [
					'description' => 'Analyze your manuscript with powerful tools to track progress and improve your story.',
					'items' => [
						['title' => 'Appearance Heatmap', 'description' => 'Visualize and discover hidden connections in your story.', 'image' => 'heatmap.webp'],
						['title' => 'Characters per Scene', 'description' => 'Discover overcrowded scenes and characters who are missing in action.', 'image' => 'character-per-scene.webp'],
						['title' => 'Word Statistics', 'description' => 'Keep track of the size of your manuscript.', 'image' => 'stats.webp'],
					],
				],
				'Collaboration' => [
					'description' => 'Work with editors, co-writers, or friends to bring your story to life together.',
					'items' => [
						['title' => 'Share with a Team', 'description' => 'Share novels or series with any team you’re a part of.', 'image' => 'share.webp'],
						['title' => 'Create a Team', 'description' => 'Invite a writing group, editing team, or share with your private membership.', 'image' => 'team.webp'],
						['title' => 'Sharing (with others)', 'description' => 'Invite an editor or friend to review or collaborate on your work.', 'image' => 'share-others.webp'],
					],
				],
			];

			return view('index', compact('features'));
		}
	}
