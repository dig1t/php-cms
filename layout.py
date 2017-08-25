# -> route
# > goto
# ~ page feature
# • description

site.info = {
    'Mutually followed users automatically become friends',
    'left side bar' = {
		'80px width',
		'white background',
		'light gray right border',
        '50x50 icons, no text'
    },
}

site.guide = {
	username = '[a-zA-Z0-9]+',
	id = '[0-9]+',
}

messaging.permissions = {
    1: 'everyone (default)',
    2: 'followers',
    3: 'following',
    4: 'nobody',
    'does not count for group chats or if recipient is a business page',
    'group chats invite can only be sent by friends',
}

# landing, unauthenticated
route['/'] = {
    'random user photo as background',
    'login form' = {
        goto: '/home' # if authenticated
    }
    ~ register button (pop up /register modal)
    ~ stats
    # of users
    # of posts
}

# login, unauthenticated
route['/login'] = {
	goto: '/home', # if authenticated
	data: [
		'username OR email OR phone number',
		'password'
	]
}

# sign up page, unauthenticated
route['/register'] = {
	goto: '/home' # if authenticated
	data: [
		'username',
		'email', # optional
		'phone number', # optional
		'password'
	]
}

# home, authenticated
route['/'] = {
	goto: '/trending',
	'user feed (posts, pictures)' = {
		onClick: 'open modal of content'
	},
	'trending (10 topics)',
	'who to follow list'
}

# messages, authenticated
route['/messages'] = {
	'listing of all private and group messages'
	'user feed (posts, pictures)' = {
		onClick: 'open modal of content'
	},
	'trending (10 topics)',
	'who to follow list'
}

# user page
route['/{username}'] = {
	'can only view if page is not private or user and viewer are friends', # following each other
	'feed' = [
		'media', # mix of posts, pictures, videos, articles, polls
		'pinned media on top',
		'on click open modal of content'
	],
	'stats' = [
		'posts',
		'followers',
		'following'
	]
}

# user post
route['/{username}/post/{id:[0-9]+}'] = {
	'more detailed page about the post',
	'load 5 max comments (scroll to load more),
}