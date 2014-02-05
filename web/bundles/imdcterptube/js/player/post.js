
//function post(pid, stime, etime, istemporal)
//{
//	this.id = pid;
//	this.startTime = stime;
//	this.endTime = etime;
//	this.isTemporal = istemporal;
//	this.color = get_random_color();
//}



function postfull(pid, pthreadid, authID, textcont, start, end,
		commdate, tempcommentbool,
		authorname, authorjoindate, color) {
	this.id = pid;
	this.parentThreadId = pthreadid;
	this.authorId = authID;
	this.content = textcont;
	this.startTime = start;
	this.endTime = end;
	this.date = commdate;
	this.isTemporal = tempcommentbool;
	this.authorName = authorname;
	this.authorJoinDate = authorjoindate;
	this.color = color;
}
