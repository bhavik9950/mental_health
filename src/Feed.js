import React, { useState, useEffect } from 'react';
import axios from 'axios';
import './health.css';

const Feed = () => {
  const [posts, setPosts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    fetchPosts();
  }, []);

  const fetchPosts = async () => {
    try {
      setLoading(true);
      const response = await axios.get('http://localhost:8080/fetch_posts.php');
      setPosts(response.data);
    } catch (err) {
      setError('Failed to fetch posts. Please try again later.');
      console.error('Error fetching posts:', err);
    } finally {
      setLoading(false);
    }
  };

  const fetchResponses = async (postId) => {
    try {
      const response = await axios.get(`http://localhost:8080/fetch_responses.php?post_id=${postId}`);
      return response.data;
    } catch (err) {
      console.error('Error fetching responses:', err);
      return [];
    }
  };

  const [responses, setResponses] = useState({});

  useEffect(() => {
    const loadAllResponses = async () => {
      const responsesData = {};
      for (const post of posts) {
        responsesData[post.id] = await fetchResponses(post.id);
      }
      setResponses(responsesData);
    };

    if (posts.length > 0) {
      loadAllResponses();
    }
  }, [posts]);

  if (loading) {
    return (
      <div className="feed">
        <h2>Community Feed</h2>
        <div className="text-center">
          <div className="spinner-border" role="status">
            <span className="visually-hidden">Loading...</span>
          </div>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="feed">
        <h2>Community Feed</h2>
        <div className="alert alert-danger" role="alert">
          {error}
        </div>
      </div>
    );
  }

  return (
    <div className="feed">
      <h2 id="heading">Community Feed</h2>
      <p className="platform-description">
        Read stories and experiences shared by our community members.
      </p>
      
      {posts.length === 0 ? (
        <div className="alert alert-info" role="alert">
          No posts available yet. Be the first to share your story!
        </div>
      ) : (
        <div className="posts-container">
          {posts.map((post) => (
            <div key={post.id} className="post">
              <div className="post-header">
                <h5 className="post-title">Anonymous Post #{post.id}</h5>
                <small className="text-muted">
                  {new Date(post.created_at).toLocaleDateString()}
                </small>
              </div>
              <div className="post-content">
                <p>{post.content}</p>
              </div>
              
              {/* Responses Section */}
              <div className="responses-section mt-3">
                <h6>Responses ({responses[post.id]?.length || 0}):</h6>
                {responses[post.id] && responses[post.id].length > 0 ? (
                  <div className="responses-list">
                    {responses[post.id].map((response) => (
                      <div key={response.id} className="response">
                        <p className="mb-1">{response.response}</p>
                        <small className="text-muted">
                          Anonymous • {new Date(response.created_at).toLocaleDateString()}
                        </small>
                      </div>
                    ))}
                  </div>
                ) : (
                  <p className="text-muted">No responses yet.</p>
                )}
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
};

export default Feed;