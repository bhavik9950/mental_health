import React, { useState } from 'react';
import axios from 'axios';
import './health.css';

const PostForm = () => {
  const [name, setName] = useState('');
  const [content, setContent] = useState('');
  const [responseMessage, setResponseMessage] = useState('');

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    try {
      const postData = new URLSearchParams();
      postData.append('name', name);
      postData.append('content', content);

      const response = await axios.post('http://localhost/mental_health_backend/post.php', postData, {
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
      });
      setResponseMessage(response.data.message);

    } catch (error) {
      console.error('Error submitting form:', error);
      setResponseMessage('There was an error submitting your form.');
    }
  };

  return (
    <div className="post-form">
      <h2 className='post'>Create a New Post</h2>
      <form onSubmit={handleSubmit}>
        <div className="form-group">
          <label htmlFor="name">Name</label>
          <input
            className='form-control'
            type="text"
            id="name"
            name="name"
            value={name}
            onChange={(e) => setName(e.target.value)}
            required
          />
        </div>
        <div className="form-group">
          <label htmlFor="content">Content</label>
          <textarea
            className='form-control mt-3'
            id="content"
            name="content"
            value={content}
            onChange={(e) => setContent(e.target.value)}
            placeholder="Describe Your Mental Health issue..."
            rows={7}
            required
          />
        </div>
        <button type="submit" id='submit' className='btn mt-3 btn-primary'>Submit</button>
      </form>
      {responseMessage && <p>{responseMessage}</p>}
    </div>
  );
};

export default PostForm;
