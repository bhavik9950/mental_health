import React, { useEffect, useState } from 'react';
import axios from 'axios';
import './health.css';

const Feed = () => {
  const [posts, setPosts] = useState([]); // Holds the array of posts fetched from the backend.
  const [error, setError] = useState(null); // Holds any error messages to be displayed to the user
  const [response, setResponse] = useState(''); //  Stores the current response that the user is typing in the modal
  const [postId, setPostId] = useState(null); // Keeps track of which post the user is responding to
  const [responses, setResponses] = useState({}); // An object that maps post IDs to their corresponding responses

  const fetchPosts = async () => {
    try {
      const response = await axios.get('http://localhost/mental_health_backend/fetch_posts.php');
      if (Array.isArray(response.data)) {
        setPosts(response.data);

        const responsesData = await Promise.all(response.data.map(post =>
          axios.get(`http://localhost/mental_health_backend/fetch_responses.php?post_id=${post.id}`)
        ));

        const responsesMap = {};
        responsesData.forEach((res, index) => {
          responsesMap[response.data[index].id] = res.data;
        });

        setResponses(responsesMap);
      } else {
        setError('Unexpected data format');
      }
    } catch (error) {
      console.error('Error fetching posts:', error);
      setError('Error fetching posts');
    }
  };
  const openModal = (id) => {
    setPostId(id);
    setResponse('');
  };
  
 useEffect(() => {
  fetchPosts();

  // Wait until Bootstrap is actually loaded
  const interval = setInterval(() => {
    if (window.bootstrap) {
      const modalElement = document.getElementById('myModal');
      if (modalElement) {
        new window.bootstrap.Modal(modalElement);
        clearInterval(interval);
      }
    }
  }, 100);

  return () => clearInterval(interval);
}, []);


  const handleResponseSubmit = async () => {
    if (!response || !postId) return;

    try {
      await axios.post('http://localhost/mental_health_backend/submit_response.php', {
        post_id: postId,
        content: response,
      });

      // Update responses in state
      setResponses((prevResponses) => ({
        ...prevResponses,
        [postId]: [...(prevResponses[postId] || []), { content: response }]
      }));

      setResponse('');
      setPostId(null);
    } catch (error) {
      console.error('Error submitting the response:', error);
      setError('Error submitting response');
    }
  };


  return (
    <div className="feed">
      <h2>Community Feed</h2>
      <p id='fd'>Read and respond to the experiences shared by others in the community.</p>
      {error && <p className="error">{error}</p>}
      {posts.length > 0 ? (
        posts.map((post) => (
          <div key={post.id} className="post">
            <b>{post.name}:</b>
            <p>{post.content}</p>

            <div className="responses">
              {Array.isArray(responses[post.id]) && responses[post.id].map((resp, index) => (
                <div key={index} className="response">
                  <p>{resp.content}</p>
                </div>
              ))}

            </div>
            <button
  id='respond-btn'
  className="btn btn-primary"
  data-bs-toggle="modal"
  data-bs-target="#myModal"
  onClick={() => openModal(post.id)}
>
  Respond
</button>


          </div>
        ))
      ) : (
        <p>No posts available.</p>
      )}

      <div className="modal" id="myModal" tabIndex={-1}>
        <div className="modal-dialog">
          <div className="modal-content">
            <div className="modal-header">
              <h4 className="modal-title">Submit Your Response</h4>
              <button type="button" className="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div className="modal-body">
              <div className="form-group">
                <textarea
                  name="response"
                  value={response}
                  onChange={(e) => setResponse(e.target.value)}
                  cols="30"
                  rows="4"
                  placeholder="Type your response here..."
                  className="form-control"
                ></textarea>
              </div>
            </div>
            <div className="modal-footer">
              <button
                type="button"
                className="btn btn-success"
                onClick={handleResponseSubmit}
                data-bs-dismiss="modal"
              >
                Save Respond
              </button>
              <button type="button" className="btn btn-danger" data-bs-dismiss="modal">Clear Respond</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Feed;
