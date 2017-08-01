<h1>Tag Server</h1>
<p>The main purpose of the project is to suggest tags based on a text (e.g. a news article). It saves time by allowing
editors to automatically enter tags. Note that the project is optimised for Arabic texts.</p>
<p>The project consists of a server and a client. The server runs and waits for requests and the
client receives requests from end-users and sends them to the server.</p>
<p>Two types of requests are possible: fetch and refresh. Refresh is for updating tags stored in the server by reading
them from database. It is useful when tags get inserted or deleted.</p>
<h2>Deployment</h2>
<p>First, edit the database connection parameters in helpers.php in order to successfully fetch tags from your database.
Then, edit the read query in helpers.php::fetchTagsFromDB().</p>
<p>You can optionally edit the stop words and pronoun words that don't take "ال" in SuggestTags.</p>
<p>Run the server in the background and send requests to the socket client. The action sent in GET can be either fetch
or refresh. In case the action is fetch, send the text in the string parameter in POST.</p>