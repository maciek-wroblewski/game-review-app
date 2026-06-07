// 1. Initialize backend tools (Axios)
import './bootstrap';

// 2. Import Bootstrap and make it globally available for inline Blade scripts
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// 4. Import and initialize UI modules
import FollowButton from './modules/follow-button';
import LikeButton from './modules/like-button';
import Lightbox from './modules/lightbox';
import TruncateText from './modules/truncate-text';
import MediaUpload from './modules/media-uploader';
import ClickableCard from './modules/clickable-card';
import LoadMore from './modules/load-more';
import PostEditForm from './modules/post-edit-form';
import PostFormActions from './modules/post-form-actions';
import PostMenu from './modules/post-menu';
import RatingMeter from './modules/rating-meter';
import RepliesContainer from './modules/replies-container';
import RepliesToggle from './modules/replies-toggle';
import ReplyToggle from './modules/reply-toggle';
import UserSearchSelector from './modules/user-search-selector';
import UserAvatar from './modules/user-avatar';

new FollowButton();
new LikeButton();
new Lightbox();
new TruncateText();
new MediaUpload();
new ClickableCard();
new LoadMore();
new PostEditForm();
new PostFormActions();
new PostMenu();
new RatingMeter();
new RepliesContainer();
new RepliesToggle();
new ReplyToggle();
new UserSearchSelector();
new UserAvatar();